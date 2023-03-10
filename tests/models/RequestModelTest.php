<?php

use Avalonium\Feedback\Models\Request;

/**
 * Feedback Model Test
 */
class RequestModelTest extends PluginTestCase
{
    /**
     * Create Model Test
     */
    public function test_create_feedback(): void
    {
        Request::truncate();

        $settings = \Avalonium\Feedback\Models\Settings::instance();
        $settings->send_to_amo = true;
        $settings->amo_client_id = '3ff05add-78e2-46ca-8db1-781f5b696a03';
        $settings->amo_client_key = '3rWP8UbVg2UEZxSWofj3TSx9cbqx3EZeasevtGHE7VbTUIVZzPVZIeGJ32mFJKPr';
        $settings->save();

        $model = Request::factory()->create();

        // Check Model
        $this->assertInstanceOf(Request::class, $model);
        $this->assertDatabaseCount($model->getTable(), 1);
        $this->assertDatabaseHas($model->getTable(), $model->getAttributes());
    }
}
