<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * Display a listing of active tenants ordered by route_order.
     */
    public function index()
{
    $tenants = Tenant::where('is_active', true)
        ->orderBy('route_order')
        ->get()
        ->map(function ($tenant) {

            $lastChecklist = DB::table('checklists')
                ->where('tenant_id', $tenant->id)
                ->orderByDesc('id')
                ->first();

            if (!$lastChecklist) {
                $tenant->status = 'pending';
            } elseif ($lastChecklist->status === 'PROBLEM') {
                $tenant->status = 'issue';
            } else {
                $tenant->status = 'done';
            }

            return $tenant;
        });

    return response()->json($tenants);
}

    /**
     * Display the specified tenant using route model binding.
     */
    public function show(Tenant $tenant)
    {
        return response()->json($tenant);
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'area' => ['required', 'string', 'max:255'],
            'top' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'left' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string'],
            'route_order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
            'updated_by' => ['sometimes', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);
        $area = trim($validated['area']);

        if ($name === '' || $area === '') {
            return response()->json([
                'message' => 'Name and area cannot be empty.'
            ], 422);
        }

        try {
            $tenant = DB::transaction(function () use ($validated, $name, $area) {
                // Lock table read to prevent race conditions on code generation
                $lastCode = Tenant::query()
                    ->lockForUpdate()
                    ->orderByDesc('code')
                    ->value('code');

                $lastNumber = $lastCode
                    ? (int) str_replace('TENANT-', '', $lastCode)
                    : 0;

                $nextNumber = $lastNumber + 1;
                $code = sprintf('TENANT-%03d', $nextNumber);

                $maxRouteOrder = Tenant::max('route_order');
                $routeOrder = $validated['route_order'] ?? (($maxRouteOrder ?? 0) + 1);

                $top = isset($validated['top']) ? max(0, min($validated['top'], 100)) : 50.00;
                $left = isset($validated['left']) ? max(0, min($validated['left'], 100)) : 50.00;

                return Tenant::create([
                    'code' => $code,
                    'name' => $name,
                    'area' => $area,
                    'top' => $top,
                    'left' => $left,
                    'status' => $validated['status'] ?? 'pending',
                    'route_order' => $routeOrder,
                    'is_active' => $validated['is_active'] ?? true,
                    'updated_by' => $validated['updated_by'] ?? (auth()->user()?->name ?? 'Admin Reporting Center'),
                    'last_position_updated_at' => null,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'tenants_code_unique')) {
                return response()->json([
                    'message' => 'Failed generating tenant code. Please retry.'
                ], 409);
            }
            throw $e;
        }

        return response()->json($tenant, 201);
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'area' => 'sometimes|string|max:255',
            'top' => 'sometimes|numeric|min:0|max:100',
            'left' => 'sometimes|numeric|min:0|max:100',
            'status' => 'sometimes|string',
            'route_order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
            'updated_by' => 'sometimes|string|max:255',
        ]);

        // Explicit whitelist of fields clients are allowed to update
        $validatedData = collect($validated)->only([
            'name',
            'area',
            'top',
            'left',
            'status',
            'route_order',
            'is_active',
            'updated_by',
        ])->toArray();

        // Empty update protection
        if (empty($validatedData)) {
            return response()->json([
                'message' => 'No changes provided.'
            ], 422);
        }

        // Coordinate audit logic
        $coordinatesChanged = array_key_exists('top', $validatedData) || array_key_exists('left', $validatedData);
        if ($coordinatesChanged) {
            $validatedData['last_position_updated_at'] = now();
        }

        // Set updated_by auditing
        if (!isset($validatedData['updated_by'])) {
            if (auth()->check()) {
                $validatedData['updated_by'] = auth()->user()->name;
            }
        }

        // DB transaction wrapper
        DB::transaction(function () use ($tenant, $validatedData) {
            $tenant->update($validatedData);
        });

        return response()->json($tenant->fresh());
    }
}
