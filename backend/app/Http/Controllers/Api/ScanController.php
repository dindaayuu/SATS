<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Bag;
use App\Models\BagDetail;
use App\Models\Activity;
use App\Models\BagLog;

class ScanController extends Controller
{

    public function dashboard()
    {
        /*
        |--------------------------------------------------------------------------
        | TAS TERSEDIA
        |--------------------------------------------------------------------------
        */
    
        $available = Bag::where('status', 'available')
            ->latest()
            ->get();
    
        /*
        |--------------------------------------------------------------------------
        | TAS DIPAKAI
        |--------------------------------------------------------------------------
        */
    
        $taken = Bag::where('status', 'taken')
            ->latest()
            ->get()
            ->map(function ($bag) {
    
                /*
                |--------------------------------------------------------------------------
                | ACTIVITY TERAKHIR
                |--------------------------------------------------------------------------
                */
    
                $activity = Activity::where('bag_id', $bag->id)
                    ->where('type', 'pickup')
                    ->latest()
                    ->first();
    
                return [
    
                    'id' => $bag->id,
    
                    'name' => $bag->name,
    
                    'name_store' => $bag->name_store,
    
                    /*
                    |--------------------------------------------------------------------------
                    | NAMA PENGAMBIL
                    |--------------------------------------------------------------------------
                    */
    
                    'employee_name' => $activity
                        ? $activity->employee_name
                        : '-',
    
                    /*
                    |--------------------------------------------------------------------------
                    | JAM PICKUP
                    |--------------------------------------------------------------------------
                    */
    
                    'pickup_time' => $activity
                        ? $activity->created_at
                        : null,
                ];
            });
    
        return response()->json([
    
            'available_bags' => $available,
    
            'used_bags' => $taken,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PICKUP SCAN
    |--------------------------------------------------------------------------
    */

    public function pickupScan(Request $request)
    {
        $request->validate([
            'barcode' => 'required'
        ]);

        $bag = Bag::where('barcode', $request->barcode)->first();

        if (!$bag) {

            return response()->json([
                'success' => false,
                'message' => 'Tas tidak ditemukan'
            ]);
        }

        if ($bag->status === 'taken') {

            return response()->json([
                'success' => false,
                'message' => 'Tas sudah digunakan'
            ]);
        }

        return response()->json([
            'success' => true,
            'bag' => $bag
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE PICKUP
    |--------------------------------------------------------------------------
    */

    public function validatePickup(Request $request)
    {
        try {
    
            $bags = $request->input('bags');
    
            $pickerName = $request->input('picker_name');
    
            /*
            |--------------------------------------------------------------------------
            | VALIDASI
            |--------------------------------------------------------------------------
            */
    
            if (!$pickerName || trim($pickerName) === '') {
    
                return response()->json([
                    'success' => false,
                    'message' => 'Nama pengambil wajib diisi'
                ]);
            }
    
            if (!$bags || count($bags) === 0) {
    
                return response()->json([
                    'success' => false,
                    'message' => 'Tas belum dipilih'
                ]);
            }
    
            /*
            |--------------------------------------------------------------------------
            | LOOP TAS
            |--------------------------------------------------------------------------
            */
    
            foreach ($bags as $bagData) {
    
                $bag = Bag::find($bagData['id']);
    
                if (!$bag) {
                    continue;
                }
    
                /*
                |--------------------------------------------------------------------------
                | UPDATE STATUS TAS
                |--------------------------------------------------------------------------
                */
    
                $bag->status = 'taken';
    
                $bag->save();
    
                /*
                |--------------------------------------------------------------------------
                | CREATE ACTIVITY
                |--------------------------------------------------------------------------
                */
    
                $activity = new Activity();
    
                $activity->employee_name = $pickerName;
    
                $activity->date = now();
    
                $activity->type = 'pickup';
    
                /*
                |--------------------------------------------------------------------------
                | INI FIX NYA
                |--------------------------------------------------------------------------
                */
    
                $activity->bag_id = $bag->id;
    
                $activity->barcode = $bag->barcode;
    
                $activity->name_store = $bag->name_store;
    
                $activity->save();
    
                /*
                |--------------------------------------------------------------------------
                | LOG
                |--------------------------------------------------------------------------
                */
    
                $log = new BagLog();
    
                $log->activity_id = $activity->id;
    
                $log->bag_id = $bag->id;
    
                $log->name_store = $bag->name_store;
    
                $log->barcode = $bag->barcode;
    
                $log->save();
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Pickup berhasil'
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN SCAN BAG
    |--------------------------------------------------------------------------
    */

    public function returnScanBag(Request $request)
    {
        $request->validate([
            'barcode' => 'required'
        ]);

        $bag = Bag::with('details')
            ->where('barcode', $request->barcode)
            ->first();

        if (!$bag) {

            return response()->json([
                'success' => false,
                'message' => 'Tas tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'bag' => $bag
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN SCAN ITEM
    |--------------------------------------------------------------------------
    */

    public function returnScanItem(Request $request)
    {
        $request->validate([
            'barcode' => 'required',
            'bag_id' => 'required'
        ]);

        $detail = BagDetail::where('bag_id', $request->bag_id)
            ->where('barcode', $request->barcode)
            ->first();

        if (!$detail) {

            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'detail' => $detail
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN SAVE
    |--------------------------------------------------------------------------
    */

    public function returnSave(Request $request)
    {
        $request->validate([
            'bag_id' => 'required',
            'employee_name' => 'required'
        ]);

        $bag = Bag::find($request->bag_id);

        if (!$bag) {

            return response()->json([
                'success' => false,
                'message' => 'Tas tidak ditemukan'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */

        if ($request->has('notes')) {

            foreach ($request->notes as $barcode => $note) {
        
                BagDetail::where(
                    'barcode',
                    $barcode
                )->update([
        
                    'condition_note' => $note
        
                ]);
            }
        }

        $bag->update([
            'status' => 'available'
        ]);

        /*
        |--------------------------------------------------------------------------
        | SIMPAN ACTIVITY
        |--------------------------------------------------------------------------
        */

        $activity = Activity::create([

            'employee_name' => $request->employee_name,

            'date' => now(),

            'type' => 'return',

            'bag_id' => $bag->id,

            'barcode' => $bag->barcode,

            'name_store' => $bag->name_store
        ]);

        /*
        |--------------------------------------------------------------------------
        | SIMPAN LOG
        |--------------------------------------------------------------------------
        */

        BagLog::create([

            'activity_id' => $activity->id,

            'bag_id' => $bag->id,

            'name_store' => $bag->name_store,

            'barcode' => $bag->barcode
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengembalian berhasil'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SCAN MODE
    |--------------------------------------------------------------------------
    */

    public function getScanMode()
    {
        $mode = \DB::table('scan_types')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'type' => $mode ? $mode->type : 'tas_only'
        ]);
    }

        /*
    |--------------------------------------------------------------------------
    | GET BAG DETAILS
    |--------------------------------------------------------------------------
    */

    public function getBagDetails($bagCode)
{
    $bag = \App\Models\Bag::where('barcode', $bagCode)
        ->first();

    if (!$bag) {
        return response()->json([
            'success' => false,
            'message' => 'Tas tidak ditemukan'
        ], 404);
    }

    $details = \App\Models\BagDetail::where('bag_id', $bag->id)
        ->get();

    return response()->json([
        'success' => true,
        'bag' => $bag,
        'details' => $details
    ]);
}

}