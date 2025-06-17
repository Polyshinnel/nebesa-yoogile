<?php

namespace App\Http\Api;

use App\Tools\RequestTool;

class WeekApi
{
    private RequestTool $requestTool;
    private string $token;

    public function __construct(RequestTool $requestTool)
    {
        $this->token = config('week.week_api');
        $this->requestTool = $requestTool;
    }

    public function createProject(string $name, string $description): array
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];
        $data = [
            'name' => $name,
            'description' => $description,
            'logo' => null,
            'isPrivate' => false,
        ];
        $url = 'https://api.weeek.net/public/v1/tm/projects';
        return $this->requestTool->requestTool('POST', $url, json_encode($data), $headers);
    }

    public function createBoard(string $name, int $projectId): array
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];
        $url = 'https://api.weeek.net/public/v1/tm/boards';
        $data = [
            'name' => $name,
            'projectId' => $projectId,
        ];
        return $this->requestTool->requestTool('POST', $url, json_encode($data), $headers);
    }

    public function getBorderColumnList(int $boardId): array
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];
        $url = 'https://api.weeek.net/public/v1/tm/board-columns?boardId='.$boardId;
        return $this->requestTool->requestTool('GET', $url, null, $headers);
    }

    public function getPortfolios(): array
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];
        $url = 'https://api.weeek.net/public/v1/tm/tasks';
        return $this->requestTool->requestTool('GET', $url, null, $headers);
    }

    public function createTask(string $title, string $description, int $projectId, int $columnBoardId): array
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];

        $url = 'https://api.weeek.net/public/v1/tm/tasks';

        $data = [
            'title' => $title,
            'description' => $description,
            'locations' => [
                [
                    'projectId' => $projectId,
                    'boardColumnId' => $columnBoardId,
                ]
            ],
            'type' => 'action'
        ];
        return $this->requestTool->requestTool('POST', $url, json_encode($data), $headers);
    }

    public function createContact(string $firstName, string $middleName, string $lastName, string $phone): array
    {
        $url = 'https://api.weeek.net/public/v1/crm/contacts';

        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];

        $data = [
            'lastName' => $lastName,
            'firstName' => $firstName,
            'middleName' => $middleName,
            'phones' => [
                $phone
            ]
        ];

        return $this->requestTool->requestTool('POST', $url, json_encode($data), $headers);
    }

    public function getUsers(): array
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];
        $url = 'https://api.weeek.net/public/v1/ws/members';
        return $this->requestTool->requestTool('GET', $url, null, $headers);
    }

    public function addAssigners($userList, $taskId): array
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}"
        ];
        $url = sprintf('https://api.weeek.net/public/v1/tm/tasks/%s/assignees', $taskId);
        $data = [
            'assignees' => $userList
        ];
        return $this->requestTool->requestTool('POST', $url, json_encode($data), $headers);
    }
}
