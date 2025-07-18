<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderEvent
{
    use Dispatchable, SerializesModels;

    public Order $order;
    public string $action; // ex: 'created', 'updated_by_client', 'status_updated'

    /**
     * Crée une nouvelle instance de l'événement.
     *
     * @param  \App\Models\Order  $order
     * @param  string  $action
     */
    public function __construct(Order $order, string $action)
    {
        $this->order = $order;
        $this->action = $action;
    }
}
