<?php namespace Avalonium\Feedback\Channels;

use Avalonium\Feedback\Models\Request;
use Illuminate\Notifications\Notification;

/**
 * Amo channel
 */
class AmoChannel
{
    /**
     * Send the given notification.
     */
    public function send(Request $notifiable, Notification $notification): void
    {
        $notification->toAmo($notifiable)->sendLead();
    }
}
