<?php namespace Avalonium\Feedback\Models;

use Model;

/**
 * Settings Model
 *
// * @property float      $margin_rate
// * @property boolean    $allow_margin_rate
 *
 * @method static instance()
 * @method static get(string $key, $default = null)
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
        'send_to_telegram' => 'required|bool',
        'telegram_bot_token' => 'required_if:send_to_telegram,1',
        'telegram_channel_id' => 'required_if:send_to_telegram,1'
    ];
}
