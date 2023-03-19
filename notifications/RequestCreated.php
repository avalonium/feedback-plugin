<?php namespace Avalonium\Feedback\Notifications;

use Avalonium\Feedback\Models\Request;
use Avalonium\Feedback\Models\Settings;
use Avalonium\Feedback\Classes\AmoHelper;
use Illuminate\Notifications\Notification;
use Avalonium\Feedback\Channels\AmoChannel;
use NotificationChannels\Telegram\TelegramMessage;

/**
 *  Send notification
 */
class RequestCreated extends Notification
{
    public function via($notifiable)
    {
        $channels = [];

        Settings::get('send_to_amo') && $channels[] = AmoChannel::class;
        Settings::get('send_to_telegram') && $channels[] = 'telegram';

        return $channels;
    }

    public function toAmo(Request $notifiable): AmoHelper
    {
        return AmoHelper::create()
            ->fillLead($notifiable->getNotificationVars());
    }

    public function toTelegram(Request $notifiable)
    {
        return TelegramMessage::create()
            ->token(Settings::get('telegram_bot_token', false))
            ->view('avalonium.feedback::notification', $notifiable->getNotificationVars());
    }
}
