<?php

namespace App\Console\Commands;

use App\Http\Controllers\CreateMoySkladWeekTask;
use Illuminate\Console\Command;

class CreateTasksFromMoySklad extends Command
{
    private CreateMoySkladWeekTask $createMoySkladWeekTask;

    public function __construct(CreateMoySkladWeekTask $createMoySkladWeekTask)
    {
        parent::__construct();
        $this->createMoySkladWeekTask = $createMoySkladWeekTask;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'week:create-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получает новые заказы из Мой Склад и передает их в Week';


    public function handle(): int
    {
        $taskInfo = $this->createMoySkladWeekTask->precessingTasks();
        $this->info($taskInfo);
        return 0;
    }
}
