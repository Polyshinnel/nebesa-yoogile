<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;

class RequestTool
{
    public function
    requestTool(string $method, string $url, $data=null, array $headers=null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'HEAD':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
                curl_setopt($ch, CURLOPT_NOBODY, true);
                break;
            case 'OPTIONS':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                break;
        }

        if ($data && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if (curl_errno($ch)) {
            $error = curl_error($ch);
            Log::error('API Error', [
                'error' => $error,
                'url' => $url
            ]);
            return [
                'response' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'response' => $response,
                'error' => null,
                'http_code' => $httpCode
            ];
        }
    }
}
