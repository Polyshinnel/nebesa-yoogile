<?php

namespace App\Http\Controllers;

use App\Http\Api\MoySkladApi;
use App\Http\Api\WeekApi;
use App\Models\MoySkladCustomers;
use App\Models\MoySkladOrder;
use Illuminate\Http\Request;

class CreateMoySkladWeekTask extends Controller
{
    private MoySkladApi $moySkladApi;
    private WeekApi $weekApi;

    public function __construct(MoySkladApi $moySkladApi, WeekApi $weekApi)
    {
        $this->moySkladApi = $moySkladApi;
        $this->weekApi = $weekApi;
    }

    public function precessingTasks(): string
    {
        $userList = [
            '9edad07c-7dfb-4b01-8b02-eb5e85eba39c',
            '9f10ce14-04e3-46d9-bf2c-6cf26c190110',
            '9f14ea94-d05e-439e-b0ba-eb166bf71539'
        ];
        $orders = $this->getMoySkladNewOrders();

        if($orders)
        {
            foreach ($orders as $order)
            {
                $this->createOrderToDb($order);
                $project = $this->weekApi->createProject($order['custom_order_name'], 'Заказ из МойСклад');
                if(!$project['response']) {
                    return "Ошибка создания проекта!";
                }
                $projectJson = json_decode($project['response'], true);
                $projectId = $projectJson['project']['id'];
                $board = $this->weekApi->createBoard($order['full_dead_name'], $projectId);

                if(!$board['response'])
                {
                    return "Ошибка создания доски!";
                }
                $boardJson = json_decode($board['response'], true);
                $boardId = $boardJson['board']['id'];
                $boardCols = $this->weekApi->getBorderColumnList($boardId);

                if(!$boardCols['response']) {
                    return "Ошибка получения колонок!";
                }

                $boardColsJson = json_decode($boardCols['response'], true);
                $columnId = $boardColsJson['boardColumns'][0]['id'];

                $taskArr = [
                    [
                        'name' => $order['order_name'],
                        'description' => $order['href']
                    ]
                ];

                if($order['project_name'] == 'КРЕМАЦИЯ' || $order['project_name'] == 'ПОХОРОНЫ')
                {
                    $taskArr[] = [
                        'name' => 'Оформление документов',
                        'description' => ''
                    ];
                    $taskArr[] = [
                        'name' => 'Предпохоронная подготовка',
                        'description' => ''
                    ];
                    $taskArr[] = [
                        'name' => 'Подготовка траурного зала',
                        'description' => ''
                    ];
                    $taskArr[] = [
                        'name' => 'Подготовка фото',
                        'description' => ''
                    ];
                    $taskArr[] = [
                        'name' => 'Табличка',
                        'description' => ''
                    ];
                    $taskArr[] = [
                        'name' => 'Комплекташка',
                        'description' => ''
                    ];
                    $taskArr[] = [
                        'name' => 'Ленты',
                        'description' => ''
                    ];
                    $taskArr[] = [
                        'name' => 'Наряд землекопу',
                        'description' => ''
                    ];

                    if($order['project_name'] == 'КРЕМАЦИЯ')
                    {
                        $taskArr[] = [
                            'name' => 'Бронь в крематории',
                            'description' => ''
                        ];
                    }
                }

                foreach ($taskArr as $task)
                {
                    $task = $this->weekApi->createTask($task['name'], $task['description'], $projectId, $columnId);
                    if(!$task['response']) {
                        return 'Ошибка создания задачи!';
                    }
                    $taskJson = json_decode($task['response'], true);
                    $taskId = $taskJson['task']['id'];
                    $this->weekApi->addAssigners($userList, $taskId);
                }



                $this->createOrderToDb($order);
            }
            $customers = $this->getMoyskladNewCustomers($orders);
            if($customers)
            {
                foreach ($customers as $customer)
                {
                    $contact = $this->weekApi->createContact($customer['name'],$customer['middle_name'],$customer['second_name'],$customer['phone']);
                    $this->createCustomerToDb($customer);
                }
            }
        }
        return "Скрипт успешно выполнен!";
    }

    private function getMoySkladNewOrders()
    {
        $orders = $this->moySkladApi->getOrders(5);
        $orders = json_decode($orders, true);
        $orders = $orders['rows'];
        $orderList = [];
        $token = $this->moySkladApi->getToken();
        foreach ($orders as $order) {
            $id = $order['id'];
            //Проверяем есть ли такой заказ
            $moyskladDbOrder = MoySkladOrder::where('order_id', $id)->first();
            if(!$moyskladDbOrder) {
                $deadName = null;
                $fullDeadName = null;
                $attributes = $order['attributes'];
                $projectName = 'N.A';
                if(isset($order['project']['meta']['href']))
                {
                    $projectUrl = $order['project']['meta']['href'];
                    $projectInfo = $this->moySkladApi->getSomeData($token, $projectUrl);
                    $projectInfo = json_decode($projectInfo, true);
                    $projectName = $projectInfo['name'];
                }



//                $positionsUrl = $order['positions']['meta']['href'];
//                $positionsInfo = $this->moySkladApi->getSomeData($token, $positionsUrl);
//                $positionsInfo = json_decode($positionsInfo, true);

                $positions = [];

//                foreach ($positionsInfo['rows'] as $position) {
//                    $positionLink = $position['assortment']['meta']['href'];
//                    $positionData = $this->moySkladApi->getSomeData($token, $positionLink);
//                    $positionData = json_decode($positionData, true);
//                    $positions[] = $positionData['name'];
//                }


                foreach ($attributes as $attr) {
                    if ($attr['name'] == 'Умерший') {
                        $fullDeadName = $attr['value'];
                        $valueStr = $attr['value'];
                        $valueArr = explode(' ', $valueStr);
                        $deadName = $valueArr[0];
                    }
                }
                $orderList[] = [
                    'id' => $order['id'],
                    'href' => $order['meta']['uuidHref'],
                    'customer_link' => $order['agent']['meta']['href'],
                    'order_name' => $order['name'],
                    'custom_order_name' => sprintf('%s ум. %s', $order['name'], $deadName),
                    'full_dead_name' => $fullDeadName,
                    'project_name' => $projectName,
                    'positions' => $positions,
                ];

            }
        }

        return $orderList;
    }

    private function getMoyskladNewCustomers(array $orders): array
    {
        $customers = [];
        foreach ($orders as $order) {
            $customer = $this->moySkladApi->getCustomer($order['customer_link']);
            $customer = json_decode($customer, true);
            $moyskladDbCustomer = MoySkladCustomers::where('phone', $customer['phone'])->first();
            if(!$moyskladDbCustomer) {
                if(!isset($customer['legalFirstName']))
                {
                    if(!isset($customer['name']))
                    {
                        continue;
                    }

                    $name = $customer['name'];
                    $nameArr = explode(' ', $name);
                    $customer['legalFirstName'] = $nameArr[1];
                    $customer['legalLastName'] = $nameArr[0];
                    $customer['legalMiddleName'] = $nameArr[2];
                }
                $customers[] = [
                    'name' => $customer['legalFirstName'],
                    'second_name' => $customer['legalLastName'],
                    'middle_name' => $customer['legalMiddleName'],
                    'phone' => $customer['phone'],
                ];
            }
        }
        return $customers;
    }

    private function createOrderToDb($order): void
    {
        $createArr = [
            'order_id' => $order['id'],
            'order_name' => $order['custom_order_name'],
            'order_link' => $order['href'],
        ];
        MoySkladOrder::create($createArr);
    }

    private function createCustomerToDb($customer): void
    {
        MoySkladCustomers::create($customer);
    }
}
