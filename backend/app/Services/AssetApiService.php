<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AssetApiService
{
    private function client()
    {
        $client = Http::acceptJson();

        if (config('services.asset_api.token')) {
            $client = $client->withToken(
                config('services.asset_api.token')
            );
        }

        return $client;
    }

    public function getBag($assetCode)
    {
        return $this->client()->get(
            config('services.asset_api.url')
            . "/integrations/sats/bags/{$assetCode}"
        );
    }
}