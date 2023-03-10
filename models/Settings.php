<?php namespace Avalonium\Feedback\Models;

use Model;
use Avalonium\Feedback\Classes\AmoHelper;

/**
 * Settings Model
 *
 * @property bool   send_to_amo
 * @property string amo_client_id
 * @property string amo_client_key
 *
 * @property bool   send_to_telegram
 * @property string telegram_bot_token
 * @property string telegram_channel_id
 *
 * @method static instance()
 * @method static get(string $key, $default = null)
 *
 * @method \October\Rain\Database\Relations\AttachOne   amo_token
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var array implement these behaviors
     */
    public $implement = [
        \System\Behaviors\SettingsModel::class
    ];

    /**
     * @var string settingsCode unique to this model
     */
    public $settingsCode = 'avalonium_feedback_settings';

    /**
     * @var string settingsFields configuration
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var array rules for validation
     */
    public $rules = [
        // AMO settings
        'send_to_amo' => 'required|bool',
        'amo_client_id' => 'required_if:send_to_amo,1',
        'amo_client_key' => 'required_if:send_to_amo,1',
        // Telegram settings
        'send_to_telegram' => 'required|bool',
        'telegram_bot_token' => 'required_if:send_to_telegram,1',
        'telegram_channel_id' => 'required_if:send_to_telegram,1'
    ];

    /**
     * Default values to set for this model
     */
    public function initSettingsData()
    {
        // AMO settings
        $this->send_to_amo = false;
        $this->amo_client_id = '';
        $this->amo_client_key = '';
        // Telegram settings
        $this->send_to_telegram = false;
        $this->telegram_bot_token = '';
        $this->telegram_channel_id = '';
    }

    /**
     * Get AmoCRM OAuth owner details
     */
    public function getAmoOwner(): string|null
    {
        return AmoHelper::create()->getAccessTokenOwnerEmail();
    }

    /**
     * Get AmoCRM OAuth owner details
     */
    public function isAmoAccessToken(): string|null
    {
        return AmoHelper::create()->isAccessTokenSet();
    }

    /**
     * Get AmoCRM OAuth button
     */
    public function getAmoOAuthButton(): string
    {
        return AmoHelper::create()->getOAuthButton(['mode' => 'popup']);
    }
}
