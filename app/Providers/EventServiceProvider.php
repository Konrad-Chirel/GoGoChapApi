<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\OrderEvent;
use App\Listeners\SendOrderNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderEvent::class => [
            SendOrderNotification::class,
        ],
    ];

    public function boot()
    {
        //
    }
}
