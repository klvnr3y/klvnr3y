<?php

namespace App\Observers;

use App\Notification;
use App\User;
class UserObserver
{
   
    public function created(User $user)
    {       

         
            // $notif = new Notification;
            // $notif->content = $user->id;
            // $notif->type = "ticket";
            // $notif->save(); 
    }
}