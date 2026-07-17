<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDetail;
use App\Models\Bag;
use App\Models\BagDetail;
use App\Services\AssetApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SatsIntegrationController extends Controller
{
    /**
     * Helper untuk menormalisasi nama (menghapus spasi, huruf kapital, dan tanda hubung)
     * agar pencocokan nama toko antara SATS dan API eksternal lebih akurat.
     */
    private function normalizeName(string $name): string
    {
        return strtolower(str_replace([' ', '-', '_'], '', $name));
    }

    /**
     * Melakukan pemetaan nama toko dari API eksternal ke nama asli di seeder SATS.
     * Digunakan karena adanya perbedaan penulisan kecil (misal: "Jenju Cafe" vs "Kafe Jenju").
     */
    private function mapStoreName(string $apiName): string
    {
        $normalized = $this->normalizeName($apiName);
        
        $nameMap = [
            'jenjucafe' => 'kafe jenju',
            'daimamiresto' => 'kedai daimami',
            'tatatitijajanan' => 'tata titi',
            'adunyalimerchandise' => 'adu nyali',
            'boothjenju' => 'kapal jenju',
            'boothringin' => 'resi waringin',
            'polahbocahicecream' => 'polah bocah',
            'boothlikaliku' => 'lika liku',
        ];

        return $nameMap[$normalized] ?? $apiName;
    }

    /**
     * Mengambil dan menyinkronkan seluruh plotting devices (tas) beserta perangkatnya.
     * Jika endpoint utama API eksternal mengalami kendala (500 Error), fungsi ini otomatis
     * beralih ke logika fallback: melakukan query scan mandiri satu per satu (barcode AST-TAS-001 s.d AST-TAS-020).
     */
    public function plotingDevices(AssetApiService $assetApi)
    {
        try {
            $response = $assetApi->plotingDevices();
            $syncedBarcodes = [];
            $useFallback = false;

            // Jika API eksternal gagal dihubungi atau mengembalikan error, gunakan metode fallback
            if ($response->failed() || !isset($response->json()['data'])) {
                Log::info('SATS Integration: main plotingDevices API failed, switching to one-by-one barcode sync fallback.');
                $useFallback = true;
            }

            if (!$useFallback) {
                // Alur Utama (jika API eksternal normal)
                $data = $response->json();
                $devices = $data['data'] ?? $data ?? [];

                foreach ($devices as $item) {
                    if (!isset($item['barcode'])) {
                        continue;
                    }

                    $storeName = $item['store_name'] ?? $item['name_store'] ?? 'SATS';

                    // 1. Sinkronisasi data tas (Bag) ke database lokal SATS
                    $bag = Bag::updateOrCreate(
                        ['barcode' => $item['barcode']],
                        [
                            'name' => $item['name'] ?? ('Tas ' . $storeName),
                            'name_store' => $storeName,
                            'status' => $item['status'] ?? 'available',
                            'is_active' => true,
                        ]
                    );

                    // 2. Cari tenant_id lokal jika nama toko pada tas terdaftar di SATS
                    $tenantId = null;
                    if (!empty($storeName)) {
                        $mappedName = $this->mapStoreName($storeName);
                        $normalizedNameStore = $this->normalizeName($mappedName);
                        
                        $tenant = Tenant::get()->first(function ($t) use ($normalizedNameStore) {
                            return $this->normalizeName($t->name) === $normalizedNameStore;
                        });
                        if ($tenant) {
                            $tenantId = $tenant->id;
                        }
                    }

                    // 3. Sinkronisasi detail perangkat di dalam tas (BagDetail) ke database lokal SATS
                    $devicesInBag = $item['assets'] ?? $item['devices'] ?? $item['bag_details'] ?? [];
                    foreach ($devicesInBag as $index => $dev) {
                        $devBarcode = $dev['asset_code'] ?? $dev['barcode'] ?? ($item['barcode'] . '-DEV-' . ($index + 1));
                        BagDetail::updateOrCreate(
                            ['barcode' => $devBarcode],
                            [
                                'bag_id' => $bag->id,
                                'asset' => $dev['asset_name'] ?? $dev['asset'] ?? $dev['name'] ?? 'Unknown Device',
                                'condition_note' => $dev['condition_note'] ?? $dev['condition'] ?? null,
                                'is_return' => 0,
                                'tenant_id' => $tenantId,
                            ]
                        );
                    }
                    $syncedBarcodes[] = $item['barcode'];
                }
            } else {
                // Alur Fallback (melakukan scan barcode satu-per-satu dari 001 s.d 020)
                for ($i = 1; $i <= 20; $i++) {
                    $barcode = 'AST-TAS-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                    $scanResponse = $assetApi->scanPlotingDevice($barcode);

                    if ($scanResponse->successful()) {
                        $resData = $scanResponse->json();
                        $item = $resData['data'] ?? $resData ?? null;

                        if ($item && isset($item['barcode'])) {
                            $storeName = $item['store_name'] ?? $item['name_store'] ?? 'SATS';

                            // 1. Sinkronisasi data tas (Bag) ke database lokal SATS
                            $bag = Bag::updateOrCreate(
                                ['barcode' => $item['barcode']],
                                [
                                    'name' => $item['name'] ?? ('Tas ' . $storeName),
                                    'name_store' => $storeName,
                                    'status' => $item['status'] ?? 'available',
                                    'is_active' => true,
                                ]
                            );

                            // 2. Cari tenant_id lokal jika nama toko pada tas terdaftar di SATS
                            $tenantId = null;
                            if (!empty($storeName)) {
                                $mappedName = $this->mapStoreName($storeName);
                                $normalizedNameStore = $this->normalizeName($mappedName);
                                
                                $tenant = Tenant::get()->first(function ($t) use ($normalizedNameStore) {
                                    return $this->normalizeName($t->name) === $normalizedNameStore;
                                });
                                if ($tenant) {
                                    $tenantId = $tenant->id;
                                }
                            }

                            // 3. Sinkronisasi detail perangkat di dalam tas (BagDetail) ke database lokal SATS
                            $devicesInBag = $item['assets'] ?? $item['devices'] ?? $item['bag_details'] ?? [];
                            foreach ($devicesInBag as $index => $dev) {
                                $devBarcode = $dev['asset_code'] ?? $dev['barcode'] ?? ($item['barcode'] . '-DEV-' . ($index + 1));
                                BagDetail::updateOrCreate(
                                    ['barcode' => $devBarcode],
                                    [
                                        'bag_id' => $bag->id,
                                        'asset' => $dev['asset_name'] ?? $dev['asset'] ?? $dev['name'] ?? 'Unknown Device',
                                        'condition_note' => $dev['condition_note'] ?? $dev['condition'] ?? null,
                                        'is_return' => 0,
                                        'tenant_id' => $tenantId,
                                    ]
                                );
                            }
                            $syncedBarcodes[] = $item['barcode'];
                        }
                    }
                }
            }

            // 4. Bersihkan tas-tas lama bawaan seeder (seperti SAT-xxx) yang tidak ada di respon API eksternal
            $otherBags = Bag::whereNotIn('barcode', $syncedBarcodes)->get();
            foreach ($otherBags as $other) {
                try {
                    $other->delete();
                } catch (\Exception $e) {
                    $other->update(['is_active' => false]);
                }
            }

            // 5. Format hasil keluaran yang siap dikonsumsi oleh frontend SATS
            $allBags = Bag::with('details')->whereIn('barcode', $syncedBarcodes)->get();
            $resultData = $allBags->map(function ($bag) {
                return [
                    'barcode' => $bag->barcode,
                    'name' => $bag->name,
                    'store_name' => $bag->name_store,
                    'status' => $bag->status,
                    'assets' => $bag->details->map(function ($detail) {
                        return [
                            'asset_code' => $detail->barcode,
                            'asset_name' => $detail->asset,
                            'condition' => $detail->condition_note ?? 'good',
                        ];
                    })->all()
                ];
            })->all();

            return response()->json([
                'success' => true,
                'message' => 'Ploting devices retrieved and synced successfully.',
                'data' => $resultData
            ], 200);
        } catch (\Exception $e) {
            Log::error('SATS Integration Error: plotingDevices', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memindai (scan) barcode tas atau perangkat tunggal secara realtime dan menyinkronkannya ke database SATS.
     */
    public function scanPlotingDevice(Request $request, AssetApiService $assetApi, $asset_code)
    {
        try {
            // Memilih pemanggilan endpoint tas atau ploting device dari API eksternal
            if ($request->is('*bags*')) {
                $response = $assetApi->scanBag($asset_code);
            } else {
                $response = $assetApi->scanPlotingDevice($asset_code);
            }

            if ($response->failed()) {
                Log::warning('SATS Integration: scanPlotingDevice API call failed', [
                    'asset_code' => $asset_code,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memindai perangkat dari API Asset Management'
                ], $response->status());
            }

            $data = $response->json();
            $item = $data['data'] ?? $data ?? null;

            if ($item && isset($item['barcode'])) {
                $storeName = $item['store_name'] ?? $item['name_store'] ?? 'SATS';

                // 1. Sinkronisasi data tas (Bag) ke database lokal SATS
                $bag = Bag::updateOrCreate(
                    ['barcode' => $item['barcode']],
                    [
                        'name' => $item['name'] ?? ('Tas ' . $storeName),
                        'name_store' => $storeName,
                        'status' => $item['status'] ?? 'available',
                        'is_active' => true,
                    ]
                );

                // 2. Cari tenant_id lokal jika nama toko pada tas terdaftar di SATS
                $tenantId = null;
                if (!empty($storeName)) {
                    $mappedName = $this->mapStoreName($storeName);
                    $normalizedNameStore = $this->normalizeName($mappedName);
                    
                    $tenant = Tenant::get()->first(function ($t) use ($normalizedNameStore) {
                        return $this->normalizeName($t->name) === $normalizedNameStore;
                    });
                    if ($tenant) {
                        $tenantId = $tenant->id;
                    }
                }

                // 3. Sinkronisasi detail perangkat di dalam tas (BagDetail) ke database lokal SATS
                $devicesInBag = $item['assets'] ?? $item['devices'] ?? $item['bag_details'] ?? [];
                foreach ($devicesInBag as $index => $dev) {
                    $devBarcode = $dev['asset_code'] ?? $dev['barcode'] ?? ($item['barcode'] . '-DEV-' . ($index + 1));
                    BagDetail::updateOrCreate(
                        ['barcode' => $devBarcode],
                        [
                            'bag_id' => $bag->id,
                            'asset' => $dev['asset_name'] ?? $dev['asset'] ?? $dev['name'] ?? 'Unknown Device',
                            'condition_note' => $dev['condition_note'] ?? $dev['condition'] ?? null,
                            'is_return' => 0,
                            'tenant_id' => $tenantId,
                        ]
                    );
                }
            }

            return response()->json($data, $response->status());
        } catch (\Exception $e) {
            Log::error('SATS Integration Error: scanPlotingDevice', [
                'asset_code' => $asset_code,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sinkronisasi data master toko (stores) dari API IT Asset Management.
     * Hanya memperbarui kode dan nama tenant yang cocok berdasarkan pencocokan nama.
     * Tidak membuat tenant baru dan tidak menghapus/menonaktifkan data asli SATS lainnya.
     */
    public function stores(AssetApiService $assetApi)
    {
        try {
            $response = $assetApi->stores();

            if ($response->failed()) {
                Log::warning('SATS Integration: stores API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung dengan API Asset Management untuk data store'
                ], $response->status());
            }

            $data = $response->json();
            $stores = $data['data'] ?? $data ?? [];

            foreach ($stores as $store) {
                if (!isset($store['code'])) {
                    continue;
                }

                // Pencocokan nama dengan tenant lokal menggunakan pemetaan nama & normalisasi
                $mappedName = $this->mapStoreName($store['name']);
                $normalizedStoreName = $this->normalizeName($mappedName);
                
                $tenant = Tenant::get()->first(function ($t) use ($normalizedStoreName) {
                    return $this->normalizeName($t->name) === $normalizedStoreName;
                });

                if ($tenant) {
                    // Update kode dan nama toko, pertahankan posisi koordinat, area, dan order asli
                    $tenant->update([
                        'code' => $store['code'],
                        'name' => $store['name'],
                        'is_active' => true,
                    ]);
                }
                // Tenant lain yang tidak ada di API dibiarkan utuh di database SATS
            }

            return response()->json($data, $response->status());
        } catch (\Exception $e) {
            Log::error('SATS Integration Error: stores', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sinkronisasi detail perangkat/aset (store package) toko ke tabel tenant_details lokal.
     */
    public function storePackage(AssetApiService $assetApi, $store_code)
    {
        try {
            $response = $assetApi->storePackage($store_code);

            if ($response->failed()) {
                Log::warning('SATS Integration: storePackage API call failed', [
                    'store_code' => $store_code,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung dengan API Asset Management untuk data package'
                ], $response->status());
            }

            $data = $response->json();
            
            // Cari tenant lokal berdasarkan kode toko
            $tenant = Tenant::where('code', $store_code)->first();
            if ($tenant) {
                // Dapatkan array aset toko dari respon API eksternal
                $devices = $data['data']['assets'] ?? $data['data']['devices'] ?? $data['assets'] ?? $data['devices'] ?? $data['data'] ?? $data ?? [];
                
                foreach ($devices as $device) {
                    $assetCode = $device['asset_code'] ?? $device['barcode'] ?? null;
                    if (!$assetCode) {
                        continue;
                    }

                    // Sinkronkan data perangkat toko ke database lokal SATS
                    TenantDetail::updateOrCreate(
                        ['asset_code' => $assetCode],
                        [
                            'tenant_id' => $tenant->id,
                            'asset_name' => $device['asset_name'] ?? $device['asset'] ?? $device['name'] ?? 'Unknown Asset',
                            'condition' => strtoupper($device['condition'] ?? 'GOOD'),
                            'is_active' => true,
                        ]
                    );
                }
            }

            return response()->json($data, $response->status());
        } catch (\Exception $e) {
            Log::error('SATS Integration Error: storePackage', [
                'store_code' => $store_code,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memuat detail perangkat tunggal secara langsung dari API eksternal IT Asset Management.
     */
    public function assetLookup(AssetApiService $assetApi, $asset_code)
    {
        try {
            $response = $assetApi->lookupAsset($asset_code);

            if ($response->failed()) {
                Log::warning('SATS Integration: assetLookup API call failed', [
                    'asset_code' => $asset_code,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat detail asset dari API eksternal'
                ], $response->status());
            }

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('SATS Integration Error: assetLookup', [
                'asset_code' => $asset_code,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
