<?php namespace Avalonium\Feedback\Seeders;

use Avalonium\Feedback\Models\Request;

/**
 * Feedback Model Seeder
 */
class RequestSeeder extends \October\Rain\Database\Updates\Seeder
{
    public function run()
    {
        Request::create([
            // Base
            'firstname' => 'Ava',
            'lastname' => 'Team',
            'email' => 'hello@avalonium.team',
            'phone' => '380980000000',
            'message' => 'Hi! We are glad to see you!',
            // Metrics
            'referer' => 'https://avalonium.team',
            'ip' => '127.0.0.1',
            'utm' => [
                'utm_source' => 'source',
                'utm_medium' => 'medium',
                'utm_campaign' => 'campaign',
                'utm_content' => 'content',
                'utm_term' => 'term'
            ]
        ]);
    }
}
