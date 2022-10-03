<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewGiftCardPurchaseEvent 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $merchant;
    public $params;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($merchant, $params, $invNumber)
    {
        $this->merchant = $merchant;
        $this->params = $params;
        $this->invNumber = $invNumber;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
