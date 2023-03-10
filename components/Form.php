<?php namespace Avalonium\Feedback\Components;

use Flash;
use Input;
use Session;
use Request;
use Redirect;
use Validator;
use Exception;
use Cms\Classes\Page;
use ValidationException;
use Cms\Classes\ComponentBase;
use Avalonium\Feedback\Classes\AmoHelper;
use Avalonium\Feedback\Models\Request as RequestModel;

/**
 * Form Component
 */
class Form extends ComponentBase
{
    public array $marks = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'
    ];

    /**
     * Component details
     */
    public function componentDetails(): array
    {
        return [
            'name' => 'Feedback Form',
            'description' => 'Display a feedback form on the page'
        ];
    }

    /**
     * Component properties
     */
    public function defineProperties(): array
    {
        return [
            'successMessage' => [
                'title'             => __('Success message'),
                'description'       => __('The message displayed on the site when form is processed'),
                'type'              => 'string',
                'default'           => __('Request successful created'),
                'showExternalParam' => false,
            ],
            'redirect' => [
                'title'       => 'Redirect to',
                'description' => 'Page name to redirect to after sending',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'amoPipeline' => [
                'title'       => 'AmoCRM pipeline',
                'description' => '',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'amoPipelineStatus' => [
                'title'       => 'AmoCRM pipeline status',
                'description' => '',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'isRequiredFirstname' => [
                'title' => __('Firstname field is required'),
                'type' => 'checkbox',
                'group' => 'Validation',
                'default' => true
            ],
            'isRequiredLastname' => [
                'title' => __('Lastname field is required'),
                'type' => 'checkbox',
                'group' => 'Validation',
                'default' => true
            ],
            'isRequiredEmail' => [
                'title' => __('Email field is required'),
                'type' => 'checkbox',
                'group' => 'Validation',
                'default' => true
            ],
            'isRequiredPhone' => [
                'title' => __('Phone field is required'),
                'type' => 'checkbox',
                'group' => 'Validation',
                'default' => true
            ],
            'isRequiredMessage' => [
                'title' => __('Message field is required'),
                'type' => 'checkbox',
                'group' => 'Validation',
                'default' => true
            ]
        ];
    }

    //
    // Options
    //

    public function getRedirectOptions(): array
    {
        return [
                '' => '- refresh page -',
                '0' => '- no redirect -'
            ] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getAmoPipelineOptions(): array
    {
        return [
                '' => '- empty pipeline -',
            ] + AmoHelper::create()->getPipelinesList();
    }

    public function getAmoPipelineStatusOptions(): array
    {
        return [
                '' => '- empty pipeline status -',
            ] + AmoHelper::create()->getPipelineStatusesList($this->property('amoPipeline'));
    }

    /**
     * Handler onRun
     */
    public function onRun(): void
    {
        $this->trackUtmMarks();
    }

    /**
     * onCreateFeedback ajax handler
     */
    public function onCreateFeedback()
    {
        try {
            $data = post();

            $rules = [
                'firstname' => ['string'],
                'lastname' => ['string'],
                'email' => ['string', 'email'],
                'phone' => ['string'],
                'message' => ['string'],
                'is_agreement_accepted' => ['accepted']
            ];

            $validation = Validator::make($data, $this->extendValidationRules($rules));

            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            $model = RequestModel::make($data);
            $model->setAttribute('referer', Request::url());
            $model->setAttribute('amo', [
                'amo_pipeline_id' => $this->property('amoPipeline'),
                'amo_pipeline_status_id' => $this->property('amoPipelineStatus'),
            ]);
            $model->setAttribute('utm', Session::get('avalonium-feedback-marks', []));
            $model->setAttribute('ip', Request::getClientIp());
            $model->save();

            Flash::success($this->property('successMessage'));

            /*
             * Redirect
             */
            if ($redirect = $this->makeRedirection(true)) {
                return $redirect;
            }

        } catch (Exception $ex) {
            if (Request::ajax()) {
                throw $ex;
            } else {
                Flash::error($ex->getMessage());
            }
        }
    }

    /**
     * Extend validation rules
     */
    private function extendValidationRules(array $rules): array
    {
        $this->property('isRequiredFirstname') && array_push($rules['firstname'], 'required');
        $this->property('isRequiredLastname') && array_push($rules['lastname'], 'required');
        $this->property('isRequiredEmail') && array_push($rules['email'], 'required');
        $this->property('isRequiredPhone') && array_push($rules['phone'], 'required');
        $this->property('isRequiredMessage') && array_push($rules['message'], 'required');

        return $rules;
    }

    /**
     * Put UTM marks to session
     */
    private function trackUtmMarks(): void
    {
        $data = Session::get('avalonium-feedback-marks', []);

        foreach ($this->marks as $mark) {
            Input::has($mark) && $data[$mark] = Input::get($mark);
        }

        Session::put('avalonium-feedback-marks', $data);
    }

    /**
     * make Redirection
     */
    protected function makeRedirection($intended = false): mixed
    {
        $method = $intended ? 'intended' : 'to';

        $property = post('redirect', $this->property('redirect'));

        // No redirect
        if ($property === '0') {
            return false;
        }

        // Refresh page
        if ($property === '') {
            return Redirect::refresh();
        }

        $redirectUrl = $this->pageUrl($property) ?: $property;

        if ($redirectUrl) {
            return Redirect::$method($redirectUrl);
        }
    }
}
