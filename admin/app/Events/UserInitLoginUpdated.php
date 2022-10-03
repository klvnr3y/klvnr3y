<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\User;

class UserInitLoginUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;
    public $eula_pdf;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $eula_pdf)
    {
        $this->user = $user;
        $this->eula_pdf = $eula_pdf;
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
