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

    /**
     * Live buyer registration type from FBR.
     *
     * @return array{ok: bool, registration_type: ?string, registration_no: ?string, status_code: ?string, raw: mixed, error: ?string}
     */
    public function getRegType(Organization $organization, string $registrationNo): array
    {
        $token = $organization->getDecryptedFbrSandboxToken();

        if (blank($token)) {
            return $this->lookupError('Add your FBR sandbox token in Org & FBR first.');
        }

        $registrationNo = trim($registrationNo);

        if ($registrationNo === '') {
            return $this->lookupError('NTN / registration number is required.');
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(config('fbr.timeout', 60))
                ->post(rtrim(config('fbr.sandbox.base_dist'), '/').'/Get_Reg_Type', [
                    'Registration_No' => $registrationNo,
                ]);

            $body = $response->json();

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'registration_type' => null,
                    'registration_no' => $registrationNo,
                    'status_code' => null,
                    'raw' => $body,
                    'error' => 'Get_Reg_Type HTTP '.$response->status(),
                ];
            }

            $type = data_get($body, 'REGISTRATION_TYPE')
                ?? data_get($body, 'registration_type')
                ?? data_get($body, 'Registration_Type');

            $normalized = is_string($type) ? trim($type) : null;
            if (is_string($normalized)) {
                $lower = strtolower($normalized);
                if ($lower === 'registered') {
                    $normalized = 'Registered';
                } elseif (in_array($lower, ['unregistered', 'un-registered'], true)) {
                    $normalized = 'Unregistered';
                }
            }

            return [
                'ok' => true,
                'registration_type' => $normalized,
                'registration_no' => data_get($body, 'REGISTRATION_NO') ?? $registrationNo,
                'status_code' => (string) (data_get($body, 'statuscode') ?? data_get($body, 'status_code') ?? ''),
                'raw' => $body,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('FBR Get_Reg_Type exception: '.$e->getMessage());

            return $this->lookupError($e->getMessage());
        }
    }

    /**
     * Live STATL / ATL-style status from FBR.
     *
     * @return array{ok: bool, status: ?string, status_code: ?string, raw: mixed, error: ?string}
     */
    public function statl(Organization $organization, string $registrationNo, ?string $date = null): array
    {
        $token = $organization->getDecryptedFbrSandboxToken();

        if (blank($token)) {
            return [
                'ok' => false,
                'status' => null,
                'status_code' => null,
                'raw' => null,
                'error' => 'Add your FBR sandbox token in Org & FBR first.',
            ];
        }

        $registrationNo = trim($registrationNo);

        if ($registrationNo === '') {
            return [
                'ok' => false,
                'status' => null,
                'status_code' => null,
                'raw' => null,
                'error' => 'NTN / registration number is required.',
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(config('fbr.timeout', 60))
                ->post(rtrim(config('fbr.sandbox.base_dist'), '/').'/statl', [
                    'regno' => $registrationNo,
                    'date' => $date ?: now()->format('Y-m-d'),
                ]);

            $body = $response->json();

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'status' => null,
                    'status_code' => null,
                    'raw' => $body,
                    'error' => 'STATL HTTP '.$response->status(),
                ];
            }

            $status = data_get($body, 'status')
                ?? data_get($body, 'Status')
                ?? data_get($body, 'STATUS');

            return [
                'ok' => true,
                'status' => is_string($status) ? trim($status) : null,
                'status_code' => (string) (
                    data_get($body, 'status code')
                    ?? data_get($body, 'status_code')
                    ?? data_get($body, 'statuscode')
                    ?? ''
                ),
                'raw' => $body,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('FBR STATL exception: '.$e->getMessage());

            return [
                'ok' => false,
                'status' => null,
                'status_code' => null,
                'raw' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Combined live lookup for invoice buyer selection.
     *
     * @return array{ok: bool, registration_type: ?string, statl_status: ?string, suggested_scenario: ?string, messages: list<string>, errors: list<string>}
     */
    public function buyerLiveStatus(Organization $organization, string $registrationNo): array
    {
        $reg = $this->getRegType($organization, $registrationNo);
        $statl = $this->statl($organization, $registrationNo);

        $messages = [];
        $errors = [];

        if (! $reg['ok'] && $reg['error']) {
            $errors[] = 'Get_Reg_Type: '.$reg['error'];
        }
        if (! $statl['ok'] && $statl['error']) {
            $errors[] = 'STATL: '.$statl['error'];
        }

        $registrationType = $reg['registration_type'] ?? null;
        $statlStatus = $statl['status'] ?? null;

        if ($registrationType === 'Unregistered') {
            $messages[] = 'Buyer appears Unregistered in FBR. Ask them to register if they can, or continue with SN002 if they insist.';
        } elseif ($registrationType === 'Registered') {
            $messages[] = 'Buyer appears Registered in FBR. SN001 is usually appropriate for standard-rate sales.';
        }

        if (is_string($statlStatus) && stripos($statlStatus, 'in-active') !== false) {
            $messages[] = 'STATL status is In-Active. Ask the buyer to make their status Active before relying on tax benefits.';
        } elseif (is_string($statlStatus) && stripos($statlStatus, 'active') !== false) {
            $messages[] = 'STATL status is Active.';
        }

        $suggested = match ($registrationType) {
            'Registered' => 'SN001',
            'Unregistered' => 'SN002',
            default => null,
        };

        return [
            'ok' => $reg['ok'] || $statl['ok'],
            'registration_type' => $registrationType,
            'statl_status' => $statlStatus,
            'suggested_scenario' => $suggested,
            'messages' => $messages,
            'errors' => $errors,
            'reg_raw' => $reg['raw'] ?? null,
            'statl_raw' => $statl['raw'] ?? null,
        ];
    }

    /**
     * @return array{ok: bool, registration_type: null, registration_no: null, status_code: null, raw: null, error: string}
     */
    private function lookupError(string $message): array
    {
        return [
            'ok' => false,
            'registration_type' => null,
            'registration_no' => null,
            'status_code' => null,
            'raw' => null,
            'error' => $message,
        ];
    }
}
