<?php


namespace App\Console\Commands;


use App\Service\AmqpService;
use Illuminate\Console\Command;

class Consumer extends Command
{
    protected $signature = 'consumer';


    public function handle(){
        $service = new AmqpService();
        $service->consumer();
    }
}
