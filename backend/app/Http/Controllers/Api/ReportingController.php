<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\BagDetail;
use Illuminate\Http\Request;
use App\Models\Checklist;

class ReportingController extends Controller
{
    public function summary()
{
    $today = now()->toDateString();

    $pickup = Activity::where(
        'type',
        'pickup'
    )
    ->whereDate(
        'created_at',
        $today
    )
    ->count();

    $return = Activity::where(
        'type',
        'return'
    )
    ->whereDate(
        'created_at',
        $today
    )
    ->count();

    $activeTenant = Activity::whereDate(
            'created_at',
            $today
        )
        ->distinct('employee_name')
        ->count('employee_name');

    $activeBag = max(
        0,
        $pickup - $return
    );

    return response()->json([
        'pickup' => $pickup,
        'return' => $return,
        'active_bag' => $activeBag,
        'active_tenant' => $activeTenant,
    ]);
}

    public function activityChart()
    {
        $pickup = Activity::selectRaw("
                HOUR(created_at) as hour,
                COUNT(*) as total
            ")
            ->where('type', 'pickup')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $return = Activity::selectRaw("
                HOUR(created_at) as hour,
                COUNT(*) as total
            ")
            ->where('type', 'return')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $labels = [];
        $pickupData = [];
        $returnData = [];

        for ($i = 8; $i <= 18; $i++) {
            $labels[] = sprintf(
                '%02d:00',
                $i
            );

            $pickupData[] =
                $pickup[$i] ?? 0;

            $returnData[] =
                $return[$i] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'pickup' => $pickupData,
            'return' => $returnData,
        ]);
    }

    public function topStores()
    {
        $stores = Activity::join(
                'bag_logs',
                'activities.id',
                '=',
                'bag_logs.activity_id'
            )
            ->selectRaw("
                bag_logs.name_store,
                COUNT(*) as total
            ")
            ->groupBy(
                'bag_logs.name_store'
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json(
            $stores
        );
    }

    public function problematicDevices()
    {
        $devices = BagDetail::selectRaw("
                asset,
                COUNT(*) as total
            ")
            ->whereNotNull(
                'condition_note'
            )
            ->where(
                'condition_note',
                '!=',
                ''
            )
            ->groupBy('asset')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $grandTotal =
            $devices->sum('total');

        $devices = $devices->map(
            function ($item)
            use ($grandTotal) {

                $item->percentage =
                    $grandTotal > 0
                        ? round(
                            ($item->total / $grandTotal)
                            * 100
                        )
                        : 0;

                return $item;
            }
        );

        return response()->json(
            $devices
        );
    }

    public function unreturnedDevices()
    {
        $devices = BagDetail::selectRaw("
                asset,
                COUNT(*) as total
            ")
            ->where(
                'is_return',
                false
            )
            ->groupBy('asset')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $grandTotal =
            $devices->sum('total');

        $devices = $devices->map(
            function ($item)
            use ($grandTotal) {

                $item->percentage =
                    $grandTotal > 0
                        ? round(
                            ($item->total / $grandTotal)
                            * 100
                        )
                        : 0;

                return $item;
            }
        );

        return response()->json(
            $devices
        );
    }

    public function dashboardHistory()
    {
        $history = Activity::with(
                'bagLogs'
            )
            ->latest()
            ->take(6)
            ->get()
            ->map(function (
                $activity
            ) {

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
            });

        return response()->json(
            $history
        );
    }

    public function transactions(Request $request)
    {
        $query = Activity::with('bagLogs.bag');

        $from = $request->query('from');
        $to = $request->query('to');

        if ($from) {
            $query->whereDate(
                'created_at',
                '>=',
                $from
            );
        }

        if ($to) {
            $query->whereDate(
                'created_at',
                '<=',
                $to
            );
        }
    
        $transactions = $query
            ->latest()
            ->paginate(5);
    
        $transactions
            ->getCollection()
            ->transform(
                function ($activity) {
    
                    $bagLog =
                        $activity
                            ->bagLogs
                            ->first();
    
                    return [
                        'id' =>
                            $activity->id,
    
                        'date' =>
                            $activity
                                ->created_at
                                ->format(
                                    'd/m/Y'
                                ),
    
                        'time' =>
                            $activity
                                ->created_at
                                ->format(
                                    'H:i'
                                ),
    
                        'name' =>
                            $activity
                                ->employee_name,
    
                        'store' =>
                            $bagLog
                                ? $bagLog
                                    ->name_store
                                : '-',
    
                        'bag' =>
                            $bagLog &&
                            $bagLog->bag
                                ? $bagLog
                                    ->bag
                                    ->barcode
                                : '-',
    
                        'type' =>
                            ucfirst(
                                $activity
                                    ->type
                            ),
    
                        'status' =>
                            ucfirst(
                                $activity
                                    ->type
                            ),
                    ];
                }
            );
    
        return response()->json(
            $transactions
        );
    }

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


        'success' => true,


        'data' => $data


    ]);

}
}