<?php namespace Avalonium\Feedback;

use App;
use Flash;
use Event;
use Backend;
use Redirect;
use System\Classes\PluginBase;
use System\Controllers\Settings;
use Avalonium\Feedback\Components\Form;
use Avalonium\Feedback\Classes\AmoHelper;
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

        Settings::extend(function($controller) {
            $controller->addDynamicMethod('onRemoveAmoAccessToken', function()
            {
                AmoHelper::removeOAuthAccessToken()
                    ? Flash::success(__("OAuth access token successful removed"))
                    : Flash::error(__('Something went wrong'));

                return Redirect::to(Backend::url('system/settings/update/avalonium/feedback/settings'));
            });
        });
    }
}
