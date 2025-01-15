<?php

namespace App\Console\Commands;

use App\Commands\YoogileAuth;
use Illuminate\Console\Command;

class AuthToYoogile extends Command
{
    private $yoogileAuth;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yoogile:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение токена авторизации в Yoogile';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(YoogileAuth $yoogileAuth)
    {
        parent::__construct();
        $this->yoogileAuth = $yoogileAuth;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->yoogileAuth->getAuth();
        return 0;
    }
}
