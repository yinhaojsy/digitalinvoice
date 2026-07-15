<?php

namespace App\Services\Fbr;

use App\Models\Organization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FbrReferenceClient
{
    /**
     * FBR province descriptions used in sellerProvince / buyerProvince.
     * Used when the live API is unavailable (no token / network error).
     *
     * @see https://gw.fbr.gov.pk/pdi/v1/provinces
     *
     * @var list<string>
     */
    public const FALLBACK_PROVINCES = [
        'AZAD JAMMU AND KASHMIR',
        'BALOCHISTAN',
        'CAPITAL TERRITORY',
        'GILGIT BALTISTAN',
        'KHYBER PAKHTUNKHWA',
        'PUNJAB',
        'SINDH',
    ];

    /**
     * @return list<string> Province descriptions accepted by FBR DI API
     */
    public function provinces(?Organization $organization = null): array
    {
        $token = $organization?->getDecryptedFbrSandboxToken();

        if (blank($token)) {
            return self::FALLBACK_PROVINCES;
        }

        $cacheKey = 'fbr.provinces.'.hash('sha256', $token);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($token) {
            try {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->timeout(config('fbr.timeout', 60))
                    ->get(rtrim(config('fbr.sandbox.base_pdi'), '/').'/provinces');

                if (! $response->successful()) {
                    Log::warning('FBR provinces API failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return self::FALLBACK_PROVINCES;
                }

                $rows = $response->json();

                if (! is_array($rows)) {
                    return self::FALLBACK_PROVINCES;
                }

                $names = collect($rows)
                    ->map(function ($row) {
                        if (! is_array($row)) {
                            return null;
                        }

                        return $row['stateProvinceDesc']
                            ?? $row['stateprovinceDesc']
                            ?? $row['StateProvinceDesc']
                            ?? null;
                    })
                    ->filter(fn ($name) => is_string($name) && filled(trim($name)))
                    ->map(fn (string $name) => trim($name))
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                return $names !== [] ? $names : self::FALLBACK_PROVINCES;
            } catch (\Throwable $e) {
                Log::warning('FBR provinces API exception: '.$e->getMessage());

                return self::FALLBACK_PROVINCES;
            }
        });
    }
}
