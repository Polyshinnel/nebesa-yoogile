<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\YoogileApiController;
use App\Http\Requests\TaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CreateTaskController extends Controller
{
    private $yoogileApi;

    public function __construct(YoogileApiController $yoogileApi)
    {
        $this->yoogileApi = $yoogileApi;
    }

    public function createTask(TaskRequest $request)
    {
        $data = $request->validated();
        $message = $data['message'];
        $string = str_replace('\\n', "\n", $message);
        $lines = explode("\n", $string);
        $fioArr = explode('</b>', $lines[1]);
        $description = "";
        foreach ($lines as $line)
        {
            $description.=$line.PHP_EOL;
        }

        $fio = trim($fioArr[1]);
        $date = date('d.m.y H:i:s');
        $boardName = sprintf('%s | %s', $date, $fio);
        $token = $this->getToken();

        $projectId = config('yoogile.project_id');
        $boardId = null;
        $columnId = null;
        $taskId = null;

        $boardId = $this->yoogileApi->createBoard($boardName, $projectId, $token);
        if($boardId)
        {
            $columns = $this->getColumnList();
            $columns = array_reverse($columns);
            foreach ($columns as $column)
            {
                $columnId = $this->yoogileApi->createColumn($column['title'], $column['color_id'], $boardId, $token);
            }

            if($columnId)
            {
                $taskId = $this->yoogileApi->createTask($columnId, $fio, $description, $token);
            }
        }

        if($taskId)
        {
            return response()->json(['message' => 'task was created', 'task_id' => $taskId]);
        }

        return response()->json(['message' => 'something went wrong']);
    }

    private function getToken(): string
    {
        if(Storage::disk('public')->exists('/auth/token.json'))
        {
            $fileContent = Storage::disk('public')->get('auth/token.json');
            $data = json_decode($fileContent, true);
            return $data['token'];
        } else {
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
            }
            return $token;
        }
    }

    private function getColumnList(): array
    {
        return [
            [
                'title' => 'Оформление похорон',
                'color_id' => 1
            ],
            [
                'title' => 'Оформление документов',
                'color_id' => 2
            ],
            [
                'title' => 'Морг',
                'color_id' => 3
            ],
            [
                'title' => 'Склад',
                'color_id' => 4
            ],
            [
                'title' => 'Перевозка',
                'color_id' => 5
            ],
            [
                'title' => 'Грузчики',
                'color_id' => 6
            ],
            [
                'title' => 'Прощание',
                'color_id' => 7
            ],
            [
                'title' => 'Транспорт',
                'color_id' => 8
            ],
            [
                'title' => 'Подготовка места захоронения',
                'color_id' => 9
            ],
        ];
    }
}
