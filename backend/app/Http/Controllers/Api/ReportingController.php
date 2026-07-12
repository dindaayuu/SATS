<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Bag;
use App\Models\BagDetail;
use App\Models\Checklist;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dashboard Summary
    |--------------------------------------------------------------------------
    */
    public function summary()
    {
        $today = now()->toDateString();


        /*
        |--------------------------------------------------------------------------
        | SATS
        |--------------------------------------------------------------------------
        */

        $pickup = Activity::where('type','pickup')
            ->whereDate('created_at',$today)
            ->count();


        $return = Activity::where('type','return')
            ->whereDate('created_at',$today)
            ->count();


        $unreturnedBag = Bag::where(
            'status',
            'taken'
        )->count();



        /*
        |--------------------------------------------------------------------------
        | CHECKLIST
        |--------------------------------------------------------------------------
        */

        $checklistDone = Checklist::whereDate(
                'check_date',
                $today
            )
            ->distinct('tenant_id')
            ->count('tenant_id');


        $totalTenant = Tenant::where(
                'is_active',
                true
            )
            ->count();


        $checklistPending =
            $totalTenant -
            $checklistDone;



        return response()->json([

            'pickup' =>
                $pickup,


            'unreturned_bag' =>
                $unreturnedBag,


            'return' =>
                $return,


            'checklist_done' =>
                $checklistDone,


            'checklist_pending' =>
                $checklistPending,

        ]);
    }




    /*
    |--------------------------------------------------------------------------
    | Pickup Return Chart
    |--------------------------------------------------------------------------
    */
    public function activityChart(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
    
        $pickup = Activity::selectRaw(
                'HOUR(created_at) as hour, COUNT(*) as total'
            )
            ->where('type', 'pickup')
            ->when(
                $from && $to,
                fn($q) => $q->whereBetween(
                    'created_at',
                    [
                        $from . ' 00:00:00',
                        $to . ' 23:59:59',
                    ]
                )
            )
            ->groupBy('hour')
            ->pluck('total', 'hour');
    
        $return = Activity::selectRaw(
                'HOUR(created_at) as hour, COUNT(*) as total'
            )
            ->where('type', 'return')
            ->when(
                $from && $to,
                fn($q) => $q->whereBetween(
                    'created_at',
                    [
                        $from . ' 00:00:00',
                        $to . ' 23:59:59',
                    ]
                )
            )
            ->groupBy('hour')
            ->pluck('total', 'hour');
    
        $labels = [];
        $pickupData = [];
        $returnData = [];
    
        for ($i = 8; $i <= 18; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            $pickupData[] = $pickup[$i] ?? 0;
            $returnData[] = $return[$i] ?? 0;
        }
    
        return response()->json([
            'labels' => $labels,
            'pickup' => $pickupData,
            'return' => $returnData,
        ]);
    }




    /*
    |--------------------------------------------------------------------------
    | Top Store Activity
    |--------------------------------------------------------------------------
    */
    public function topStores(Request $request)
{
    $from = $request->from;
    $to = $request->to;

    return Activity::join(
            'bag_logs',
            'activities.id',
            '=',
            'bag_logs.activity_id'
        )
        ->when(
            $from && $to,
            fn($q) => $q->whereBetween(
                'activities.created_at',
                [
                    $from . ' 00:00:00',
                    $to . ' 23:59:59',
                ]
            )
        )
        ->selectRaw(
            'bag_logs.name_store as name, COUNT(*) as total'
        )
        ->groupBy('bag_logs.name_store')
        ->orderByDesc('total')
        ->limit(5)
        ->get();
}




    /*
    |--------------------------------------------------------------------------
    | Problem Device
    |--------------------------------------------------------------------------
    */
    public function problematicDevices(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
    
        $devices = BagDetail::selectRaw(
                'asset, COUNT(*) as total'
            )
            ->whereNotNull('condition_note')
            ->where('condition_note', '!=', '')
            ->when(
                $from && $to,
                fn($q) => $q->whereBetween(
                    'updated_at',
                    [
                        $from . ' 00:00:00',
                        $to . ' 23:59:59',
                    ]
                )
            )
            ->groupBy('asset')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    
        return $devices;
    }




    /*
    |--------------------------------------------------------------------------
    | Device Belum Return
    |--------------------------------------------------------------------------
    */
    public function unreturnedDevices(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
    
        $devices = BagDetail::selectRaw(
                'asset, COUNT(*) as total'
            )
            ->where('is_return', false)
            ->when(
                $from && $to,
                fn($q) => $q->whereBetween(
                    'updated_at',
                    [
                        $from . ' 00:00:00',
                        $to . ' 23:59:59',
                    ]
                )
            )
            ->groupBy('asset')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    
        return $devices;
    }




    /*
    |--------------------------------------------------------------------------
    | Dashboard History SATS
    |--------------------------------------------------------------------------
    */
    public function dashboardHistory()
    {
        $today = now()->toDateString();
    
        $history = Activity::with("bagLogs")
            ->whereDate("created_at", $today)
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($activity) {
    
                $bagLog = $activity->bagLogs->first();
    
                return [
                    "time" => $activity->created_at->format("H:i"),
                    "name" => $activity->employee_name,
                    "store" => $bagLog?->name_store ?? "-",
                    "status" => ucfirst($activity->type),
                ];
            });
    
        return response()->json($history);
    }




    /*
    |--------------------------------------------------------------------------
    | Report SATS Detail
    |--------------------------------------------------------------------------
    */
    public function transactions(Request $request)
    {
        $query = Activity::with('bagLogs.bag');

        if ($request->filled('from') && $request->filled('to')) {
        
            $query->whereBetween(
                'created_at',
                [
                    $request->from . ' 00:00:00',
                    $request->to . ' 23:59:59',
                ]
            );
        
        }
        
        $transactions =
            $query
            ->latest()
            ->paginate(5);

        return response()->json(
            $transactions
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Checklist History
    |--------------------------------------------------------------------------
    */
    public function checklistHistory()
    {
        $data = Checklist::with([

                'tenant',
                'bag',
                'details.problemType',
                'details.replacement'

            ])
            ->latest()
            ->get();



        return response()->json([

            'success' =>
                true,


            'data' =>
                $data,

        ]);
    }

    public function dashboardChecklistHistory()
{
    $today = now()->toDateString();

    $history = Checklist::with("tenant")
        ->whereDate("check_date", $today)
        ->latest()
        ->take(8)
        ->get()
        ->map(function ($item) {

            return [

                "finish_time" =>
                    optional($item->finish_time)->format("H:i")
                    ?? optional($item->updated_at)->format("H:i"),

                "pic" =>
                    $item->pic_name,

                "tenant" =>
                    optional($item->tenant)->name,

                "total_device" =>
                    $item->details()->count(),

                "status" =>
                    $item->details()
                        ->where("condition", "problem")
                        ->exists()
                        ? "PROBLEM"
                        : "DONE",
            ];

        });

    return response()->json($history);
}
}