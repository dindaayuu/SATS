<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Bag;
use App\Models\BagDetail;
use App\Models\Activity;
use App\Models\BagLog;
use App\Models\DeviceReturnHistory;



class ScanController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        $available = Bag::where('status', 'available')
            ->latest()
            ->get();

        $taken = Bag::where('status', 'taken')
            ->latest()
            ->get()
            ->map(function ($bag) {

                $activity = Activity::where('bag_id', $bag->id)
                    ->where('type', 'pickup')
                    ->latest()
                    ->first();

                return [
                    'id'            => $bag->id,
                    'name'          => $bag->name,
                    'name_store'    => $bag->name_store,
                    'employee_name' => $activity ? $activity->employee_name : '-',
                    'pickup_time'   => $activity ? $activity->created_at : null,
                ];
            });

        return response()->json([
            'available_bags' => $available,
            'used_bags'      => $taken,
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

        $keyword = trim($request->barcode);

        $bag = Bag::where('barcode', $keyword)
            ->orWhere('name_store', $keyword)
            ->first();

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
            'bag'     => $bag
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE PICKUP
    | Reset semua is_return = 0 agar siap untuk return berikutnya
    |--------------------------------------------------------------------------
    */

    public function validatePickup(Request $request)
    {
        try {

            $bags       = $request->input('bags');
            $pickerName = $request->input('picker_name');

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

            foreach ($bags as $bagData) {

                $bag = Bag::find($bagData['id']);

                if (!$bag) {
                    continue;
                }

                /*
                |--------------------------------------------------------------
                | RESET is_return = 0 untuk semua device dalam tas
                | Gunakan integer 0, bukan false, agar konsisten dengan tinyint
                |--------------------------------------------------------------
                */

                BagDetail::where('bag_id', $bag->id)
                    ->update(['is_return' => 0]);

                /*
                |--------------------------------------------------------------
                | UPDATE STATUS TAS
                |--------------------------------------------------------------
                */

                $bag->status = 'taken';
                $bag->save();

                /*
                |--------------------------------------------------------------
                | CREATE ACTIVITY
                |--------------------------------------------------------------
                */

                $activity               = new Activity();
                $activity->employee_name = $pickerName;
                $activity->date         = now();
                $activity->type         = 'pickup';
                $activity->bag_id       = $bag->id;
                $activity->barcode      = $bag->barcode;
                $activity->name_store   = $bag->name_store;
                $activity->save();

                /*
                |--------------------------------------------------------------
                | CREATE LOG
                |--------------------------------------------------------------
                */

                $log              = new BagLog();
                $log->activity_id = $activity->id;
                $log->bag_id      = $bag->id;
                $log->name_store  = $bag->name_store;
                $log->barcode     = $bag->barcode;
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

        $keyword = trim($request->barcode);

        $bag = Bag::with('details')
            ->where('barcode', $keyword)
            ->orWhere('name_store', $keyword)
            ->first();

        if (!$bag) {
            return response()->json([
                'success' => false,
                'message' => 'Tas tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'bag'     => $bag
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN SCAN ITEM
    | Update is_return = 1 untuk device yang sudah dikembalikan
    |--------------------------------------------------------------------------
    */

    public function returnScanItem(Request $request)
    {
        $request->validate([
            'barcode' => 'required',
            'bag_id' => 'required'
        ]);
    
        $detail = BagDetail::where('bag_id', $request->bag_id)
            ->where('barcode', trim($request->barcode))
            ->first();
    
        if (!$detail) {
    
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ]);
        }

        \Log::info('ITEM FOUND', [
            'id' => $detail->id,
            'barcode' => $detail->barcode,
            'is_return_before' => $detail->is_return
        ]);
        
        $detail->is_return = 1;
            $detail->save();

            \Log::info('ITEM SAVED', [
                'id' => $detail->id,
                'barcode' => $detail->barcode,
                'is_return_after' => $detail->fresh()->is_return
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN SAVE
    | Validasi is_return hanya aktif jika mode = with_detail
    | Mode tas_only langsung simpan tanpa cek device
    |--------------------------------------------------------------------------
    */

    public function returnSave(Request $request)
    {
        $request->validate([
            'bag_id'        => 'required',
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
        |----------------------------------------------------------------------
        | PROSES CATATAN KERUSAKAN
        | Berjalan untuk semua mode (tas_only maupun with_detail)
        |----------------------------------------------------------------------
        */

        if ($request->has('notes')) {

            foreach ($request->notes as $barcode => $note) {

                BagDetail::where('barcode', $barcode)
                    ->update(['condition_note' => $note]);

                if (!empty(trim($note))) {

                    $detail = BagDetail::where('barcode', $barcode)->first();

                    $message =
                        "🚨 LAPORAN KERUSAKAN DEVICE\n\n" .
                        "Store : {$bag->name_store}\n\n" .
                        "Tas : {$bag->barcode}\n\n" .
                        "Device : {$barcode}\n\n" .
                        "Asset : {$detail->asset}\n\n" .
                        "Catatan :\n{$note}\n\n" .
                        "Pelapor :\n{$request->employee_name}\n\n" .
                        "Waktu :\n" . now()->format('d-m-Y H:i');

                    $this->sendWhatsapp($message);
                }
            }
        }

        /*
        |----------------------------------------------------------------------
        | CEK SCAN MODE
        | Validasi device (is_return) hanya aktif jika mode = with_detail
        | Mode tas_only langsung lanjut tanpa cek
        |----------------------------------------------------------------------
        */

        $modeRow  = \DB::table('scan_types')->latest()->first();
        $scanType = $modeRow ? $modeRow->type : 'tas_only';

        if ($scanType === 'with_detail') {

            $notReturned = BagDetail::where('bag_id', $bag->id)
                ->where('is_return', 0)
                ->count();

            if ($notReturned > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Masih ada ' . $notReturned . ' device yang belum dikembalikan'
                ]);
            }
        }

        /*
        |----------------------------------------------------------------------
        | UPDATE STATUS TAS MENJADI AVAILABLE
        |----------------------------------------------------------------------
        */

        $bag->status = 'available';
        $bag->save();

        /*
        |----------------------------------------------------------------------
        | SIMPAN ACTIVITY RETURN
        |----------------------------------------------------------------------
        */

        $activity = Activity::create([
            'employee_name' => $request->employee_name,
            'date'          => now(),
            'type'          => 'return',
            'bag_id'        => $bag->id,
            'barcode'       => $bag->barcode,
            'name_store'    => $bag->name_store,
        ]);

        /*
        |----------------------------------------------------------------------
        | SIMPAN LOG
        |----------------------------------------------------------------------
        */

        BagLog::create([
            'activity_id' => $activity->id,
            'bag_id'      => $bag->id,
            'name_store'  => $bag->name_store,
            'barcode'     => $bag->barcode,
        ]);

        /*
|--------------------------------------------------------------------------
| SIMPAN HISTORY DEVICE RETURN
|--------------------------------------------------------------------------
*/

$details = BagDetail::where(
    'bag_id',
    $bag->id
)->get();

foreach ($details as $detail) {

    \App\Models\DeviceReturnHistory::create([

        'activity_id'   => $activity->id,

        'bag_id'        => $bag->id,

        'bag_detail_id' => $detail->id,

        'asset'         => $detail->asset,

        'barcode'       => $detail->barcode,

        'is_return'     => $detail->is_return,

        'condition_note'=> $detail->condition_note,

        'employee_name' => $request->employee_name,

        'returned_at'   => now(),

    ]);

}

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
        $mode = \DB::table('scan_types')->latest()->first();

        return response()->json([
            'success' => true,
            'type'    => $mode ? $mode->type : 'tas_only'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET BAG DETAILS
    |--------------------------------------------------------------------------
    */

    public function getBagDetails($bagCode)
    {
        $bag = Bag::where('barcode', $bagCode)->first();

        if (!$bag) {
            return response()->json([
                'success' => false,
                'message' => 'Tas tidak ditemukan'
            ], 404);
        }

        $details = BagDetail::where('bag_id', $bag->id)->get();

        return response()->json([
            'success' => true,
            'bag'     => $bag,
            'details' => $details
        ]);
    }
    

    /*
    |--------------------------------------------------------------------------
    | SEND WHATSAPP
    |--------------------------------------------------------------------------
    */

    private function sendWhatsapp($message)
    {
        try {
            Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN')
            ])->post('https://api.fonnte.com/send', [
                'target'  => '6283856991213',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            \Log::error('WA Error : ' . $e->getMessage());
        }
    }
}