<?php namespace Avalonium\Feedback\Controllers;

use Flash;
use BackendMenu;
use ApplicationException;
use Backend\Classes\Controller;
use Avalonium\Feedback\Models\Log;
use Avalonium\Feedback\Models\Request;

/**
 * Requests Backend Controller
 */
class Requests extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var string relationConfig file
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['avalonium.feedback.requests'];

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Avalonium.Feedback', 'feedback', 'requests');
    }

    public function index()
    {
        $this->vars['scoreboard'] = Request::getScoreboardData();
        $this->asExtension('ListController')->index();
    }

    /**
     * Process request ajax handler
     */
    public function preview_onProcess(): mixed
    {
        $this->formGetModel()->process();
        Flash::success(__("Request successful processed"));

        return array_merge($this->formRefreshFields(['logs']), [
            '#toolbar' => $this->makePartial('preview_toolbar'),
        ]);
    }

    /**
     * Cancel request ajax handler
     */
    public function preview_onCancel(): mixed
    {
        $this->formGetModel()->cancel();
        Flash::success(__("Request successful canceled"));

        return array_merge($this->formRefreshFields(['logs']), [
            '#toolbar' => $this->makePartial('preview_toolbar'),
        ]);
    }

    /**
     * Ajax Handler onViewDetails
     */
    public function preview_onViewLogDetails(): mixed
    {
        if (!$model = Log::find(post('record_id'))) {
            throw new ApplicationException('Model does not found');
        }

        $this->vars['details'] = $model->details;

        return $this->makePartial('modal_log_details');
    }
}
