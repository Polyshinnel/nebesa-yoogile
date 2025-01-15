<?php

namespace App\Commands;

use App\Http\Controllers\Api\YoogileApiController;
use Illuminate\Support\Facades\Storage;

class YoogileAuth
{
    private $yoogileApi;

    public function __construct(YoogileApiController $yoogileApi)
    {
        $this->yoogileApi = $yoogileApi;
    }

    public function getAuth()
    {
        $login = config('yoogile.login');
        $password = config('yoogile.password');
        $companyName = config('yoogile.company_name');

        $token = $this->yoogileApi->getCompanyToken($login, $password, $companyName);
        if($token)
        {
            $tokenArr = [
                'token' => $token
            ];
            $jsonData = json_encode($tokenArr, JSON_PRETTY_PRINT);
            Storage::disk('public')->put('auth/token.json', $jsonData);
            echo PHP_EOL;
            echo 'token: '.$token;
            echo PHP_EOL;
            return true;
        } else {
            echo PHP_EOL;
            echo 'Ошибка получения токена!';
            echo PHP_EOL;
        }
        return false;
    }
}
