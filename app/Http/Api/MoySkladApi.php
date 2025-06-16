<?php

namespace App\Http\Api;

use App\Tools\RequestTool;

class MoySkladApi
{
    private string $login;
    private string $password;
    private RequestTool $tool;

    public function __construct(RequestTool $tool)
    {
        $this->login = config('moysklad.login');
        $this->password = config('moysklad.password');
        $this->tool = $tool;
    }

    private function getToken(): string | false
    {
        $url = 'https://api.moysklad.ru/api/remap/1.2/security/token';
        $headers = [
            'Accept-Encoding: gzip',
            'Authorization: Basic ' . base64_encode($this->login . ':' . $this->password),
        ];
        $data = $this->tool->requestTool('POST', $url, null, $headers);
        if ($data['response'])
        {
            $json = json_decode($data['response'], true);
            return $json['access_token'];
        }
        return false;
    }

    public function getOrders(int $limit = 10, int $offset = 0, string $sortColumn = 'updated', string $sortDirection = 'desc'): array | string
    {
        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/customerorder';
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];
        $queryParams = http_build_query($params);
        $url = sprintf('%s?%s&order=%s,%s', $url, $queryParams,$sortColumn,$sortDirection);
        $token = $this->getToken();
        if($token)
        {
            $headers = [
                'Accept-Encoding: gzip',
                'Authorization: Bearer '.$token
            ];
            $data = $this->tool->requestTool('GET', $url, null, $headers);
            if ($data['response']){
                // Распаковываем gzip-сжатые данные
                $decoded = gzdecode($data['response']);
                if ($decoded === false) {
                    return [
                        'message' => 'something went wrong',
                        'error' => 'Ошибка распаковки gzip данных'
                    ];
                }
                return $decoded;
            } else {
                return [
                    'message' => 'something went wrong',
                    'error' => 'Ошибка получения заказов'
                ];
            }
        }
        return [
            'message' => 'something went wrong',
            'error' => 'Ошибка получения токена'
        ];
    }

    public function getCustomer($customerUrl): array | string
    {
        $token = $this->getToken();
        if($token) {
            $headers = [
                'Accept-Encoding: gzip',
                'Authorization: Bearer ' . $token
            ];

            $data = $this->tool->requestTool('GET', $customerUrl, null, $headers);
            if ($data['response']){
                // Распаковываем gzip-сжатые данные
                $decoded = gzdecode($data['response']);
                if ($decoded === false) {
                    return [
                        'message' => 'something went wrong',
                        'error' => 'Ошибка распаковки gzip данных'
                    ];
                }
                return $decoded;
            } else {
                return [
                    'message' => 'something went wrong',
                    'error' => 'Ошибка получения контакта'
                ];
            }

        }

        return [
            'message' => 'something went wrong',
            'error' => 'Ошибка получения токена'
        ];
    }
}
