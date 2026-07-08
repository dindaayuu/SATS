<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ChecklistDetail;
use App\Models\DeviceReplacement;


class DeviceReplacementController extends Controller
{


    public function replace(Request $request)
    {


        $request->validate([

            'checklist_detail_id'
                => 'required',

            'new_asset_code'
                => 'required',

            'new_device_name'
                => 'required',

            'replaced_by'
                => 'required',

        ]);



        DB::beginTransaction();



        try {


            /*
            Ambil data device yang bermasalah
            dari hasil checklist
            */
            $detail =
                ChecklistDetail::with(
                    'bagDetail'
                )
                ->findOrFail(
                    $request
                        ->checklist_detail_id
                );



            $bagDevice =
                $detail->bagDetail;



            /*
            Catat history pergantian
            */
            $replacement =
                DeviceReplacement::create([


                    'checklist_detail_id'
                        =>
                        $detail->id,



                    'bag_id'
                        =>
                        $bagDevice
                            ->bag_id,



                    'device_type'
                        =>
                        $detail
                            ->device_name_snapshot,



                    'old_asset_code'
                        =>
                        $detail
                            ->asset_code_snapshot,



                    'old_device_name'
                        =>
                        $detail
                            ->device_name_snapshot,



                    'new_asset_code'
                        =>
                        $request
                            ->new_asset_code,



                    'new_device_name'
                        =>
                        $request
                            ->new_device_name,



                    'reason'
                        =>
                        $request
                            ->reason,



                    'replaced_by'
                        =>
                        $request
                            ->replaced_by,



                    'replacement_time'
                        =>
                        now(),

                ]);




            /*
            Update isi tas SATS
            supaya return membaca device baru
            */
            $bagDevice->update([


                'barcode'
                    =>
                    $request
                        ->new_asset_code,


                'asset'
                    =>
                    $request
                        ->new_device_name,


                'condition_note'
                    =>
                    null,

            ]);




            DB::commit();



            return response()->json([


                'success' => true,


                'message'
                    =>
                    'Pergantian device berhasil',


                'data'
                    =>
                    $replacement


            ]);



        } catch (\Exception $e) {



            DB::rollBack();



            return response()->json([


                'success'
                    =>
                    false,


                'message'
                    =>
                    $e->getMessage()


            ], 500);



        }


    }


}