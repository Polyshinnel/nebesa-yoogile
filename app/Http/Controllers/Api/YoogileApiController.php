<?php

namespace App\Http\Controllers\Api;

use App\Helpers\RequestHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class YoogileApiController extends Controller
{
    private $requestHelper;

    public function __construct(RequestHelper $requestHelper)
    {
        $this->requestHelper = $requestHelper;
    }

    public function getCompanyId(string $login, string $password, string $companyName): string
    {
        $companyId = '';
        $url = 'https://ru.yougile.com/api-v2/auth/companies';
        $headers = [
            "Content-Type: application/json"
        ];
        $requestData = [
            'login' => $login,
            'password' => $password,
            'name' => $companyName
        ];
        $result = $this->requestHelper->sendPostRequest($url, json_encode($requestData), $headers);
        if($result)
        {
            $resArr = json_decode($result, true);
            if(isset($resArr['content'][0]['id']))
            {
                $companyId = $resArr['content'][0]['id'];
            }
        }
        return $companyId;
    }

    public function getCompanyToken(string $login, string $password, string $companyName)
    {
        $token = '';
        $url = 'https://ru.yougile.com/api-v2/auth/keys';
        $companyId = $this->getCompanyId($login, $password, $companyName);
        if($companyId)
        {
            $requestData = [
                'login' => $login,
                'password' => $password,
                'companyId' => $companyId
            ];
            $headers = [
                "Content-Type: application/json"
            ];
            $result = $this->requestHelper->sendPostRequest($url, json_encode($requestData), $headers);
            if($result)
            {
                $resArr = json_decode($result, true);
                if(isset($resArr['key']))
                {
                    $token = $resArr['key'];
                }
            }
        }
        return $token;
    }

    public function createBoard($title, $projectId, $token)
    {
        $url = 'https://ru.yougile.com/api-v2/boards';
        $requestedData = [
            'title' => $title,
            'projectId' => $projectId
        ];

        $headers = [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ];

        $response = $this->requestHelper->sendPostRequest($url, json_encode($requestedData), $headers);
        if($response)
        {
            $responseArr = json_decode($response, true);
            return $responseArr['id'];
        }

        return null;
    }

    public function createColumn($title, $colorId, $boardId, $token)
    {
        $url = 'https://ru.yougile.com/api-v2/columns';
        $requestedData = [
            'title' => $title,
            'color' => $colorId,
            'boardId' => $boardId
        ];

        $headers = [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ];

        $response = $this->requestHelper->sendPostRequest($url, json_encode($requestedData), $headers);
        if($response)
        {
            $responseArr = json_decode($response, true);
            return $responseArr['id'];
        }
        return false;
    }

    public function createTask($columnId, $title, $description, $token)
    {
        $url = 'https://ru.yougile.com/api-v2/tasks';
        $requestedData = [
            'title' => $title,
            'columnId' => $columnId,
            'description' => $description
        ];

        $headers = [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ];

        $response = $this->requestHelper->sendPostRequest($url, json_encode($requestedData), $headers);
        if($response)
        {
            $responseArr = json_decode($response, true);
            return $responseArr['id'];
        }
        return null;
    }
}
