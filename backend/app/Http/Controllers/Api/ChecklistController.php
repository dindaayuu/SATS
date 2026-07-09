<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Tenant;
use App\Models\Bag;
use App\Models\Checklist;
use App\Models\ChecklistDetail;
use App\Models\ProblemType;
use App\Models\BagDetail;
use App\Models\TenantDetail;

class ChecklistController extends Controller
{

    public function tenants()
    {
        $tenants = Tenant::query()
            ->orderBy('id')
            ->get();
    
        return response()->json([
            'success' => true,
            'message' => 'Tenant checklist berhasil diambil',
            'data' => $tenants
        ]);
    }
    
    
    public function detailTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
    
        $tenantName = strtolower(
            str_replace(
                ['-', ' '],
                '',
                $tenant->name
            )
        );
    
        $bag = Bag::with('details')
            ->get()
            ->first(function ($item) use ($tenantName) {
    
                $storeName = strtolower(
                    str_replace(
                        ['-', ' '],
                        '',
                        $item->name_store
                    )
                );
    
                return $storeName === $tenantName;
            });
    
    
        if ($bag) {
    
            return response()->json([
    
                'success' => true,
    
                'source' => 'BAG',
    
                'data' => [
    
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'area' => $tenant->area,
                    ],
    
                    'bag' => $bag,
    
                    'devices' =>
                        $bag->details
                        ->map(function($item){
    
                            return [
    
                                'id' =>
                                    $item->id,
    
                                'asset' =>
                                    $item->asset,
    
                                'barcode' =>
                                    $item->barcode,
    
                                'condition' =>
                                    $item->condition,
    
                                'source_type' =>
                                    'BAG'
    
                            ];
    
                        })
    
                ]
    
            ]);
    
        }
    
    
        $devices =
            $tenant
            ->details()
            ->where(
                'is_active',
                true
            )
            ->get();
    
    
        return response()->json([
    
            'success' => true,
    
            'source' => 'TENANT',
    
            'data' => [
    
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'area' => $tenant->area,
                ],
    
                'bag' => null,
    
                'devices' =>
                    $devices
                    ->map(function($item){
    
                        return [
    
                            'id' =>
                                $item->id,
    
                            'asset' =>
                                $item->asset_name,
    
                            'barcode' =>
                                $item->asset_code,
    
                            'condition' =>
                                $item->condition,
    
                            'source_type' =>
                                'TENANT'
    
                        ];
    
                    })
    
            ]
    
        ]);
    }

    public function submit(Request $request)
{
    $data = $request->validate([
        'tenant_id'=>'required|exists:tenants,id',
        'bag_id'=>'nullable|exists:bags,id',
        'pic_name'=>'required|string',
        'start_time'=>'required',
        'overall_note'=>'nullable|string',

        'devices'=>'nullable|array',

        'devices.*.bag_detail_id'=>'nullable',
        'devices.*.tenant_detail_id'=>'nullable',
        'devices.*.source_type'=>'nullable',

        'devices.*.asset'=>'nullable',
        'devices.*.barcode'=>'nullable',
        'devices.*.condition'=>'nullable',

        'devices.*.problem_type_id'=>'nullable',
        'devices.*.note'=>'nullable',

        'devices.*.replacement'=>'nullable',
    ]);


    DB::beginTransaction();


    try{

        $hasProblem = collect(
            $data['devices'] ?? []
        )
        ->contains(function($device){

            return 
            ($device['condition'] ?? 'GOOD')
            === 'PROBLEM';

        });


        $checklist = Checklist::create([

            'tenant_id'=>
                $data['tenant_id'],

            'bag_id'=>
                $data['bag_id']
                ?? null,

            'pic_name'=>
                $data['pic_name'],

            'check_date'=>
                Carbon::today(),

            'start_time'=>
                $data['start_time'],

            'finish_time'=>
                now()->format('H:i:s'),

            'status'=>
                $hasProblem
                ? 'PROBLEM'
                : 'DONE',

            'overall_note'=>
                $data['overall_note']
                ?? null,

        ]);


        foreach(
            $data['devices'] ?? []
            as $device
        ){

            $source =
                $device['source_type']
                ?? 'BAG';


            $detail =
            ChecklistDetail::create([

                'checklist_id'=>
                    $checklist->id,


                'bag_detail_id'=>
                    $source === 'BAG'
                    ? $device['bag_detail_id']
                    : null,


                'tenant_detail_id'=>
                    $source === 'TENANT'
                    ? $device['tenant_detail_id']
                    : null,


                'source_type'=>
                    $source,


                'device_name_snapshot'=>
                    $device['asset']
                    ?? 'Device',


                'asset_code_snapshot'=>
                    $device['barcode']
                    ?? '-',


                'condition'=>
                    $device['condition']
                    ?? 'GOOD',


                'problem_type_id'=>
                    $device['problem_type_id']
                    ?? null,


                'custom_note'=>
                    $device['note']
                    ?? null,

            ]);


            if(
                isset($device['replacement'])
            ){


                \App\Models\DeviceReplacement::create([

                    'checklist_detail_id'=>
                        $detail->id,


                    'bag_id'=>
                        $data['bag_id']
                        ?? null,


                    'device_type'=>
                        $device['asset'],


                    'old_asset_code'=>
                        $device['barcode'],


                    'old_device_name'=>
                        $device['asset'],


                    'new_asset_code'=>
                        $device['replacement']
                        ['asset_code'],


                    'new_device_name'=>
                        $device['replacement']
                        ['device_name'],


                    'reason'=>
                        $device['note'],


                    'replaced_by'=>
                        $data['pic_name'],


                    'replacement_time'=>
                        now(),

                ]);


                if($source === 'BAG'){

                    BagDetail::find(
                        $device['bag_detail_id']
                    )
                    ?->update([

                        'barcode'=>
                            $device['replacement']
                            ['asset_code'],

                        'asset'=>
                            $device['replacement']
                            ['device_name'],

                    ]);

                }


                if($source === 'TENANT'){

                    TenantDetail::find(
                        $device['tenant_detail_id']
                    )
                    ?->update([

                        'asset_code'=>
                            $device['replacement']
                            ['asset_code'],

                        'asset_name'=>
                            $device['replacement']
                            ['device_name'],

                    ]);

                }

            }

        }


        DB::commit();


        return response()->json([

            'success'=>true,

            'message'=>
                'Checklist berhasil disimpan',

            'data'=>
                $checklist->load(
                    'details'
                )

        ]);


    }catch(\Exception $e){

        DB::rollBack();


        return response()->json([

            'success'=>false,

            'message'=>
                $e->getMessage()

        ],500);

    }
}

        public function problemTypes()
    {

        $problems = ProblemType::where(
                'is_active',
                true
            )
            ->get();


        return response()->json([

            'success' => true,


            'data' => $problems

        ]);

    }

        public function report()
    {
        $data = Checklist::with([
            'tenant',
            'details.problemType',
            'details.replacement'
        ])
        ->latest()
        ->get()
        ->map(function($item){

            return [

                'id'=>$item->id,

                'tenant'=>$item->tenant->name,

                'area'=>$item->tenant->area,

                'overall_note'=>$item->overall_note,

                'pic'=>$item->pic_name,

                'date'=>$item->check_date,

                'start_time'=>$item->start_time,

                'finish_time'=>$item->finish_time,

                'status'=>
                    $item
                    ->details
                    ->where('condition','PROBLEM')
                    ->count() > 0
                    ? 'PROBLEM'
                    : 'DONE',

                'total_device'=>
                    $item->details->count(),

                'total_problem'=>
                    $item
                    ->details
                    ->where(
                        'condition',
                        'PROBLEM'
                    )
                    ->count(),


                'details'=>
                    $item
                    ->details
                    ->map(function($detail){

                        return [

                            'device'=>
                                $detail
                                ->device_name_snapshot,

                            'barcode'=>
                                $detail
                                ->asset_code_snapshot,


                            'condition'=>
                                $detail->condition,


                            'problem'=>
                                $detail
                                ->problemType
                                ->name
                                ?? null,


                            'note'=>
                                $detail
                                ->custom_note,


                            'replacement'=>

                                $detail->replacement
                                ?

                                [

                                    'old_device'=>
                                        $detail
                                        ->replacement
                                        ->old_device_name,


                                    'old_code'=>
                                        $detail
                                        ->replacement
                                        ->old_asset_code,


                                    'new_device'=>
                                        $detail
                                        ->replacement
                                        ->new_device_name,


                                    'new_code'=>
                                        $detail
                                        ->replacement
                                        ->new_asset_code,


                                    'reason'=>
                                        $detail
                                        ->replacement
                                        ->reason,


                                    'replaced_by'=>
                                        $detail
                                        ->replacement
                                        ->replaced_by,


                                    'time'=>
                                        $detail
                                        ->replacement
                                        ->replacement_time,

                                ]

                                :

                                null

                        ];

                    })

            ];

        });

        return response()->json([

            'success'=>true,

            'data'=>$data

        ]);
    }
        public function dashboard()
        {
            $today = Carbon::today();

            $totalTenant = Tenant::count();

            $checkedToday = Checklist::whereDate(
                'check_date',
                $today
            )
            ->distinct('tenant_id')
            ->count('tenant_id');

            $progress = $totalTenant > 0
                ? round(($checkedToday / $totalTenant) * 100)
                : 0;

            $areas = Tenant::select('area')
                ->distinct()
                ->get()
                ->map(function ($area) use ($today) {

                    $tenants = Tenant::where(
                            'area',
                            $area->area
                        )
                        ->orderBy('route_order')
                        ->get()
                        ->map(function ($tenant) use ($today) {

                            $checklist = Checklist::where(
                                    'tenant_id',
                                    $tenant->id
                                )
                                ->whereDate(
                                    'check_date',
                                    $today
                                )
                                ->latest()
                                ->first();

                            return [
                                'id' => $tenant->id,
                                'name' => $tenant->name,
                                'status' => $checklist
                                    ? $checklist->status
                                    : 'PENDING',
                            ];
                        });

                    $total = $tenants->count();

                    $checked = $tenants
                        ->where(
                            'status',
                            '!=',
                            'PENDING'
                        )
                        ->count();

                    return [
                        'name' => $area->area,
                        'total' => $total,
                        'checked' => $checked,
                        'progress' => $total > 0
                            ? round(($checked / $total) * 100)
                            : 0,

                        'status' =>
                            $checked === 0
                            ? 'PENDING'
                            : (
                                $checked === $total
                                ? 'DONE'
                                : 'PROCESS'
                            ),

                        'tenants' => $tenants,
                    ];
                });

            return response()->json([
                'totalTenant' => $totalTenant,
                'checkedToday' => $checkedToday,
                'progress' => $progress,
                'areas' => $areas,
            ]);
    }
}