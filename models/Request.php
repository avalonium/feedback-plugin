<?php namespace Avalonium\Feedback\Models;

use DB;
use Str;
use Model;
use Event;
use Cache;
use ApplicationException;
use Avalonium\Feedback\Factories\RequestFactory;
use Avalonium\Feedback\Notifications\RequestCreated;

/**
 * Request Model
 *
 * @property-read int   $id
 * @property-read int   $uuid
 * @property string     $status
 * @property string     $number
 * @property string     $firstname
 * @property string     $lastname
 * @property string     $email
 * @property string     $phone
 * @property string     $message
 * @property string     $referer
 * @property string     $ip
 * @property array      $amo
 * @property array      $utm
 *
 * @property-read bool  $is_new
 * @property-read \Illuminate\Support\Collection logs
 *
 * @property-read \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Support\Carbon $deleted_at
 *
 * @method \October\Rain\Database\Relations\MorphMany   logs
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class Request extends Model
{
    use \Illuminate\Notifications\Notifiable;
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    const STATUS_NEW = 'new';
    const STATUS_PROCESSED = 'processed';
    const STATUS_CANCELED = 'canceled';

    const SCOREBOARD_CACHE_KEY = 'avalonium.feedback::requests.scoreboard';

    /**
     * @var string table associated with the model
     */
    public $table = 'avalonium_feedback_requests';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        // Base
        'firstname',
        'lastname',
        'email',
        'phone',
        'message',
        // Metrics
        'referer',
        'ip',
        'amo',
        'utm'
    ];

    /**
     * @var array Json fields
     */
    protected $jsonable  = [
        'amo',
        'utm'
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'uuid' => 'string|between:1,255',
        'number' => 'string',
        'status' => 'string|in:new,processed,canceled',
        // Base
        'firstname' => 'string|nullable|between:1,255',
        'lastname' => 'string|nullable|between:1,255',
        'email' => 'string|email|nullable|between:1,255',
        'phone' => 'string|nullable|between:1,255',
        'message' => 'string|nullable|between:1,255',
        // Metrics
        'referer' => 'url|nullable|between:1,255',
        'ip' => 'ip|nullable|between:1,255'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'uuid' => 'string',
        'number' => 'string',
        'status' => 'string',
        // Base
        'firstname' => 'string',
        'lastname' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'message' => 'string',
        // Metrics
        'referer' => 'string',
        'ip' => 'string'
    ];

    /**
     * @var array dates attributes that should be mutated to dates
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    //
    // Relations
    //

    public $morphMany = [
        'logs' => [
            Log::class, 'name' => 'loggable'
        ]
    ];

    //
    // Events
    //

    public function beforeCreate()
    {
        $this->updateStatus(self::STATUS_NEW);
        $this->uuid = Str::orderedUuid()->toString();
    }

    public function afterCreate()
    {
        $this->touchNumber();
        Event::fire('avalonium.feedback.request_created', [$this]);

        $this->notify(new RequestCreated($this));
    }

    public function afterSave()
    {
        Cache::forget(self::SCOREBOARD_CACHE_KEY);
    }

    //
    // Mutators
    //

    public function getIsNewAttribute(): bool
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * Update exchange status
     */
    private function updateStatus(string $status): void
    {
        $this->setAttribute('status', $status);
    }

    /**
     * Touch number
     */
    private function touchNumber(): void
    {
        $this->setAttribute('number', str('#')->append(str_pad($this->id, 6, "0", STR_PAD_LEFT))->value())->save();
    }

    /**
     * Process Exchange
     */
    public function process(): void
    {
        if (!$this->is_new) {
            throw new ApplicationException('Only new request can be processed!');
        }

        $this->setAttribute('status', self::STATUS_PROCESSED)->save();
        Event::fire('avalonium.feedback.request_processed', [$this]);
    }

    /**
     * Cancel Exchange
     */
    public function cancel(): void
    {
        if (!$this->is_new) {
            throw new ApplicationException('Only new request can be cancelled!');
        }

        $this->setAttribute('status', self::STATUS_CANCELED)->save();
        Event::fire('avalonium.feedback.request_canceled', [$this]);
    }

    /**
     * Returns the variables available when sending a user notification.
     */
    public function getNotificationVars(): array
    {
        return [
            // Base
            'uuid' => $this->uuid,
            'number' => $this->number,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            // Metrics
            'referer' => $this->referer,
            'ip' => $this->ip,
            // UTM
            'utm_source' => array_get($this->utm, 'utm_source'),
            'utm_medium' => array_get($this->utm, 'utm_medium'),
            'utm_campaign' => array_get($this->utm, 'utm_campaign'),
            'utm_content' => array_get($this->utm, 'utm_content'),
            'utm_term' => array_get($this->utm, 'utm_term'),
            // AmoCRM
            'amo_pipeline_id' => array_get($this->amo, 'amo_pipeline_id'),
            'amo_pipeline_status_id' => array_get($this->amo, 'amo_pipeline_status_id'),
            // Timestamp
            'created_at' => $this->created_at->format('Y-m-d h:m')
        ];
    }

    /**
     * Get Telegram channel ID
     */
    private function routeNotificationForTelegram(): string|null
    {
        return Settings::get('telegram_channel_id');
    }

    /**
     * Count New Requests
     */
    public static function countNewRequests(): int
    {
        return self::where('status', self::STATUS_NEW)->count();
    }

    /**
     * Get Scoreboard data
     */
    public static function getScoreboardData()
    {
        if (Cache::missing(self::SCOREBOARD_CACHE_KEY)) {
            Cache::add(self::SCOREBOARD_CACHE_KEY,
                DB::table('avalonium_feedback_requests')
                    ->select(DB::raw('count(*) as requests_count'))
                    ->addSelect(DB::raw('sum(case when status = "'.self::STATUS_NEW.'" then 1 else 0 end) as new_requests_count'))
                    ->addSelect(DB::raw('sum(case when status = "'.self::STATUS_PROCESSED.'" then 1 else 0 end) as processed_requests_count'))
                    ->addSelect(DB::raw('sum(case when status = "'.self::STATUS_CANCELED.'" then 1 else 0 end) as canceled_requests_count'))
                    ->first());
        }

        return Cache::get(self::SCOREBOARD_CACHE_KEY);
    }

    /**
     * Get Model Factory
     */
    protected static function newFactory(): RequestFactory
    {
        return RequestFactory::new();
    }
}
