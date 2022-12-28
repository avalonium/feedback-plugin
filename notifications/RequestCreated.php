<?php namespace Avalonium\Feedback\Notifications;

use Avalonium\Feedback\Models\Request;
use Avalonium\Feedback\Models\Settings;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

/**
 *  Send notification
 */
class RequestCreated extends Notification
{
    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram(Request $notifiable)
    {
        return TelegramMessage::create()
            ->token(Settings::get('telegram_bot_token', false))
            ->view('avalonium.feedback::notification', $notifiable->getNotificationVars());
    }
}
