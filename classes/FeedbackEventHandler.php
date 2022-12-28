<?php namespace Avalonium\Feedback\Classes;

use Avalonium\Feedback\Models\Request;

/**
 * Event handler for Feedback Model
 */
class FeedbackEventHandler
{
    /**
     * Handle onCreate Feedback
     */
    public function onCreated(Request $model): void
    {
        $model->logs()->create([
            'type' => 'created',
            'message' => __('New request with number :number was created', ['number' => $model->number]),
            'details' => [
                'firstname' => $model->firstname,
                'lastname' => $model->lastname,
                'email' => $model->email,
                'phone' => $model->phone,
                'message' => $model->message,
                'referer' => $model->referer,
                'ip' => $model->ip,
            ]
        ]);
    }

    /**
     * Handle onCanceled Exchange
     */
    public function onCanceled(Request $model): void
    {
        $model->logs()->create([
            'type' => 'updated',
            'message' => __('Request with number :number has been canceled', ['number' => $model->number]),
            'details' => []
        ]);
    }

    /**
     * Handle onProcessed Exchange
     */
    public function onProcessed(Request $model): void
    {
        $model->logs()->create([
            'type' => 'updated',
            'message' => __('Request with number :number has been processed', ['number' => $model->number]),
            'details' => []
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(\Illuminate\Events\Dispatcher $events): void
    {
        $events->listen('avalonium.feedback.request_created', [FeedbackEventHandler::class, 'onCreated']);
        $events->listen('avalonium.feedback.request_canceled', [FeedbackEventHandler::class, 'onCanceled']);
        $events->listen('avalonium.feedback.request_processed', [FeedbackEventHandler::class, 'onProcessed']);
    }
}
