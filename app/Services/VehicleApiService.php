<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VehicleApiService
{
    protected string $baseUrl = 'https://app.ankshipping.com/api/vehicles';

    /**
     * Resolve vehicle model attributes and pictures by VIN.
     */
    public function lookupByVin(string $vin): array
    {
        $vin = strtoupper(trim($vin));

        if (empty($vin)) {
            return [
                'success' => false,
                'vin' => $vin,
                'make' => null,
                'model' => null,
                'year' => null,
                'pictures' => [],
                'message' => 'VIN is required.',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get("{$this->baseUrl}/{$vin}/pictures");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => (bool) ($data['success'] ?? true),
                    'vin' => $data['vin'] ?? $vin,
                    'make' => $data['make'] ?? null,
                    'model' => $data['model'] ?? null,
                    'year' => isset($data['year']) && is_numeric($data['year']) ? (int) $data['year'] : null,
                    'pictures' => $data['pictures'] ?? [],
                    'message' => null,
                ];
            }

            $errorData = $response->json();
            $message = 'No vehicle or pictures found for the specified VIN on ANK Shipping server.';

            if (isset($errorData['message']) && ! str_contains($errorData['message'], 'could not be found')) {
                $message = $errorData['message'];
            }

            return [
                'success' => false,
                'vin' => $vin,
                'make' => null,
                'model' => null,
                'year' => null,
                'pictures' => [],
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            Log::error("Vehicle API Lookup Failed for VIN {$vin}: {$e->getMessage()}");

            return [
                'success' => false,
                'vin' => $vin,
                'make' => null,
                'model' => null,
                'year' => null,
                'pictures' => [],
                'message' => 'Vehicle lookup API service temporarily unavailable.',
            ];
        }
    }
}
