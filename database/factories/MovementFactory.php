<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Movement;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Movement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $customer = Customer::factory()->create();
        
        return [
            'customer_id' => $customer->id,
            'amount' => $this->faker->randomFloat(2),
        ];
    }
}
