<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AssetApiService
{
    /**
     * HTTP Client
     */
    private function client()
    {
        return Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => config('services.asset_api.key'),
            ]);
    }

    /**
     * Scan Tas + Detail Device
     */
    public function scanBag(string $assetCode)
    {
        return $this->client()->get(
            config('services.asset_api.url')
            . "/integrations/sats/ploting-devices/scan/{$assetCode}"
        );
    }

    /**
     * Lookup Asset
     */
    public function lookupAsset(string $assetCode)
    {
        return $this->client()->get(
            config('services.asset_api.url')
            . "/integrations/sats/assets/lookup/{$assetCode}"
        );
    }

    /**
     * Semua Ploting Device
     */
    public function plotingDevices()
    {
        return $this->client()->get(
            config('services.asset_api.url')
            . "/integrations/sats/ploting-devices"
        );
    }

    /**
     * Daftar Store
     */
    public function stores()
    {
        return $this->client()->get(
            config('services.asset_api.url')
            . "/integrations/sats/stores"
        );
    }

    /**
     * Package Store
     */
    public function storePackage(string $storeCode)
    {
        return $this->client()->get(
            config('services.asset_api.url')
            . "/integrations/sats/store-packages/{$storeCode}"
        );
    }
}