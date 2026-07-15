<?php

namespace App\Services\Fbr;

use App\Models\FbrSubmission;
use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FbrSandboxClient
{
    public function __construct(
        private readonly InvoicePayloadBuilder $payloadBuilder,
    ) {}

    /**
     * @return array{submission: FbrSubmission, response: array<string, mixed>|null, http_status: int|null, success: bool}
     */
    public function validate(Invoice $invoice, Organization $organization): array
    {
        return $this->send('validate', $invoice, $organization);
    }

    /**
     * @return array{submission: FbrSubmission, response: array<string, mixed>|null, http_status: int|null, success: bool}
     */
    public function post(Invoice $invoice, Organization $organization): array
    {
        return $this->send('post', $invoice, $organization);
    }

    /**
     * @return array{submission: FbrSubmission, response: array<string, mixed>|null, http_status: int|null, success: bool}
     */
    private function send(string $action, Invoice $invoice, Organization $organization): array
    {
        if (config('fbr.environment') !== 'sandbox') {
            throw new RuntimeException('Only FBR sandbox is enabled in this application version.');
        }

        $token = $organization->getDecryptedFbrSandboxToken();

        if (blank($token)) {
            throw new RuntimeException('Add your FBR sandbox Bearer token in organization settings.');
        }

        $url = match ($action) {
            'validate' => config('fbr.sandbox.validate_url'),
            'post' => config('fbr.sandbox.post_url'),
            default => throw new RuntimeException("Unknown FBR action [{$action}]."),
        };

        $payload = $this->payloadBuilder->build($invoice, $organization);

        try {
            /** @var Response $httpResponse */
            $httpResponse = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(config('fbr.timeout', 60))
                ->post($url, $payload);

            $rawBody = $httpResponse->body();
            $body = $httpResponse->json();
            $httpStatus = $httpResponse->status();
        } catch (\Throwable $e) {
            $submission = FbrSubmission::create([
                'organization_id' => $organization->id,
                'invoice_id' => $invoice->id,
                'action' => $action,
                'environment' => 'sandbox',
                'http_status' => null,
                'request_payload' => $payload,
                'response_body' => null,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            return [
                'submission' => $submission,
                'response' => null,
                'http_status' => null,
                'success' => false,
            ];
        }

        $success = $this->isSuccessful($body, $httpStatus);
        $fbrInvoiceNumber = data_get($body, 'invoiceNumber');
        $errorMessage = $this->extractError($body, $rawBody);
        $storedBody = is_array($body)
            ? $body
            : ['raw' => $rawBody !== '' ? $rawBody : '(empty body)'];

        $submission = FbrSubmission::create([
            'organization_id' => $organization->id,
            'invoice_id' => $invoice->id,
            'action' => $action,
            'environment' => 'sandbox',
            'http_status' => $httpStatus,
            'request_payload' => $payload,
            'response_body' => $storedBody,
            'success' => $success,
            'fbr_invoice_number' => $fbrInvoiceNumber,
            'error_message' => $success ? null : $errorMessage,
        ]);

        return [
            'submission' => $submission,
            'response' => is_array($body) ? $body : null,
            'http_status' => $httpStatus,
            'success' => $success,
        ];
    }

    private function isSuccessful(mixed $body, int $httpStatus): bool
    {
        if ($httpStatus !== 200 || ! is_array($body)) {
            return false;
        }

        $statusCode = (string) data_get($body, 'validationResponse.statusCode', '');
        $status = strtolower((string) data_get($body, 'validationResponse.status', ''));

        if ($statusCode !== '00' || $status !== 'valid') {
            return false;
        }

        $itemStatuses = data_get($body, 'validationResponse.invoiceStatuses');

        if (is_array($itemStatuses)) {
            foreach ($itemStatuses as $item) {
                if ((string) data_get($item, 'statusCode') !== '00') {
                    return false;
                }
            }
        }

        return true;
    }

    private function extractError(mixed $body, string $rawBody = ''): string
    {
        if (! is_array($body)) {
            $snippet = $this->snippet($rawBody !== '' ? $rawBody : '(empty body)');

            return 'Unexpected FBR response. Raw: '.$snippet;
        }

        $headerError = data_get($body, 'validationResponse.error');
        if (filled($headerError)) {
            $code = data_get($body, 'validationResponse.errorCode');

            return trim(($code ? "[{$code}] " : '').$headerError);
        }

        $messages = [];
        foreach ((array) data_get($body, 'validationResponse.invoiceStatuses', []) as $item) {
            if ((string) data_get($item, 'statusCode') !== '00') {
                $code = data_get($item, 'errorCode');
                $error = data_get($item, 'error');
                $sNo = data_get($item, 'itemSNo');
                $messages[] = trim('Item '.$sNo.': '.($code ? "[{$code}] " : '').$error);
            }
        }

        if ($messages !== []) {
            return implode(' | ', $messages);
        }

        foreach (['message', 'error', 'Error', 'Message', 'statusMessage'] as $key) {
            if (filled(data_get($body, $key))) {
                return (string) data_get($body, $key);
            }
        }

        $status = data_get($body, 'validationResponse.status');
        $statusCode = data_get($body, 'validationResponse.statusCode');
        if (filled($status) || filled($statusCode)) {
            return trim('FBR rejected the invoice. status='.(string) $status.' statusCode='.(string) $statusCode);
        }

        return 'FBR rejected the invoice. Raw: '.$this->snippet(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    private function snippet(string $text, int $max = 500): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        if ($text === '') {
            return '(empty body)';
        }

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max).'…';
    }
}
