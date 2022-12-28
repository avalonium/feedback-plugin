<?php namespace Avalonium\Feedback\Factories;

use Avalonium\Feedback\Models\Request;

/**
 * Feedback Model Factory
 */
class RequestFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    /**
     * Factory Model
     */
    protected $model = Request::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            // Base
            'firstname' => fake()->firstName,
            'lastname' => fake()->lastName,
            'email' => fake()->email,
            'phone' => fake()->phoneNumber,
            'message' => fake()->paragraph,
            // Metrics
            'referer' => fake()->url,
            'ip' => fake()->ipv4,
            'utm' => [
                'utm_source' => 'source'
            ]
        ];
    }
}
