<?php namespace Avalonium\Feedback\Models;

use Model;
use BackendAuth;
use Backend\Models\User as BackendUser;

/**
 * Log Model
 *
 * @property int        $id
 * @property string     $type
 * @property string     $message
 * @property string     $details
 *
 * @property-read BackendUser   $backend_user
 * @property-read \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Support\Carbon $updated_at
 *
 * @method \October\Rain\Database\Relations\BelongsTo   backend_user
 * @method \October\Rain\Database\Relations\MorphTo     loggable
 *
 * @mixin \Eloquent
 */
class Log extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table associated with the model
     */
    public $table = 'avalonium_feedback_logs';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'type',
        'message',
        'details'
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'type' => 'required|string',
        'message' => 'required|string',
        'details' => 'array',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'type' => 'string',
        'message' => 'string',
        'details' => 'string'
    ];

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = [
        'details'
    ];

    /**
     * @var array dates attributes that should be mutated to dates
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    //
    // Relations
    //

    public $belongsTo = [
        'backend_user' => BackendUser::class
    ];

    public $morphTo = [
        'loggable' => []
    ];

    //
    // Events
    //

    public function beforeCreate()
    {
        $this->backend_user = BackendAuth::getUser();
    }
}
