<?php namespace Avalonium\Feedback;

use App;
use Event;
use System\Classes\PluginBase;
use Avalonium\Feedback\Components\Form;
use Avalonium\Feedback\Classes\FeedbackEventHandler;
use Illuminate\Notifications\NotificationServiceProvider;
use NotificationChannels\Telegram\TelegramServiceProvider;

/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Register Plugin Components
     */
    public function registerComponents(): array
    {
        return [
            Form::class => 'feedbackForm'
        ];
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot(): void
    {
        Event::subscribe(FeedbackEventHandler::class);

        // Register Providers
        App::register(NotificationServiceProvider::class);
        App::register(TelegramServiceProvider::class);
    }
}
