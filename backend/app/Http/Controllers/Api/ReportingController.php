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
    public function activityChart()
    {
        $pickup = Activity::selectRaw(
                'HOUR(created_at) as hour, COUNT(*) as total'
            )
            ->where('type','pickup')
            ->groupBy('hour')
            ->pluck(
                'total',
                'hour'
            );


        $return = Activity::selectRaw(
                'HOUR(created_at) as hour, COUNT(*) as total'
            )
            ->where('type','return')
            ->groupBy('hour')
            ->pluck(
                'total',
                'hour'
            );


        $labels = [];
        $pickupData = [];
        $returnData = [];


        for(
            $i = 8;
            $i <= 18;
            $i++
        ){

            $labels[] =
                sprintf(
                    '%02d:00',
                    $i
                );


            $pickupData[] =
                $pickup[$i] ?? 0;


            $returnData[] =
                $return[$i] ?? 0;
        }



        return response()->json([

            'labels' =>
                $labels,

            'pickup' =>
                $pickupData,

            'return' =>
                $returnData,

        ]);
    }




    /*
    |--------------------------------------------------------------------------
    | Top Store Activity
    |--------------------------------------------------------------------------
    */
    public function topStores()
    {
        return Activity::join(
                'bag_logs',
                'activities.id',
                '=',
                'bag_logs.activity_id'
            )
            ->selectRaw(
                'bag_logs.name_store, COUNT(*) as total'
            )
            ->groupBy(
                'bag_logs.name_store'
            )
            ->orderByDesc(
                'total'
            )
            ->limit(5)
            ->get();
    }




    /*
    |--------------------------------------------------------------------------
    | Problem Device
    |--------------------------------------------------------------------------
    */
    public function problematicDevices()
    {
        $devices = BagDetail::selectRaw(
                'asset, COUNT(*) as total'
            )
            ->whereNotNull(
                'condition_note'
            )
            ->where(
                'condition_note',
                '!=',
                ''
            )
            ->groupBy(
                'asset'
            )
            ->orderByDesc(
                'total'
            )
            ->limit(5)
            ->get();


        $total =
            $devices->sum(
                'total'
            );


        return $devices->map(
            function($item) use($total){

                $item->percentage =
                    $total > 0
                    ? round(
                        (
                            $item->total /
                            $total
                        ) * 100
                    )
                    : 0;


                return $item;
            }
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Device Belum Return
    |--------------------------------------------------------------------------
    */
    public function unreturnedDevices()
    {
        $devices = BagDetail::selectRaw(
                'asset, COUNT(*) as total'
            )
            ->where(
                'is_return',
                false
            )
            ->groupBy(
                'asset'
            )
            ->orderByDesc(
                'total'
            )
            ->limit(5)
            ->get();



        $total =
            $devices->sum(
                'total'
            );



        return $devices->map(
            function($item) use($total){

                $item->percentage =
                    $total > 0
                    ? round(
                        (
                            $item->total /
                            $total
                        ) * 100
                    )
                    : 0;


                return $item;
            }
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Dashboard History SATS
    |--------------------------------------------------------------------------
    */
    public function dashboardHistory()
    {
        $history = Activity::with(
                'bagLogs'
            )
            ->latest()
            ->take(6)
            ->get()
            ->map(
                function($activity){


                    $bagLog =
                        $activity
                        ->bagLogs
                        ->first();



                    return [

                        'time' =>
                            $activity
                            ->created_at
                            ->format('H:i'),


                        'name' =>
                            $activity
                            ->employee_name,


                        'store' =>
                            $bagLog
                            ? $bagLog->name_store
                            : '-',


                        'status' =>
                            ucfirst(
                                $activity->type
                            ),

                    ];
                }
            );



        return response()->json(
            $history
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Report SATS Detail
    |--------------------------------------------------------------------------
    */
    public function transactions(Request $request)
    {
        $query =
            Activity::with(
                'bagLogs.bag'
            );


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
}