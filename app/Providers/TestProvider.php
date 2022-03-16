<?php

namespace App\Providers;

use App\Service\Origin\Mysql;
use App\Service\Origin\MysqlManager;
use Illuminate\Support\ServiceProvider;

class TestProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //如果类不依赖任务接口，则不需要把他们绑定到容器中
        //绑定依赖至IOC容器
        $this->app->bind(MysqlManager::class, function ($app) {
            return new MysqlManager(Mysql::instance() );
        });
//        $this->app->bind('mysqlmg',function($app){
//            return $app->make(MysqlManager::class);
//        });

//        //单例绑定
//        $this->app->singleton(MysqlManager::class, function ($app) {
//            return new Transistor($app->make(PodcastParser::class));
//        });

        //实例绑定
//        $service = new Transistor(new PodcastParser);
//        $this->app->instance(Transistor::class, $service);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

//        var_dump("this is a test provider");
//        $user = Auth::guard('api')->user();
//        var_dump("user:");
//        var_dump($user);
        //


    }
}
