<?php

use App\Models\Customer;
use App\Models\Movement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('makes deposit correctly', function () {
    $movement = Movement::factory()->make();

    $response = $this->post('/api/movements', $movement->toArray());

    $response->assertStatus(201);
    $this->assertDatabaseHas('movements', $movement->toArray());
});

it('makes deposit empty fields', function () {
    $response = $this->post('/api/movements');
    $response->assertStatus(400);
});

it('every third deposit with bonus', function () {
    $customer = Customer::factory()->create();
    $movements = Movement::factory()->count(3)->make([
        'customer_id' => $customer->id,
    ]);

    $response = null;
    foreach ($movements as $movement) {
        $response = $this->post('/api/movements', $movement->toArray());
    }

    expect($response['results']['promo_amount'])->toBeGreaterThan(0);
    $this->assertDatabaseHas('movements', [
        'customer_id' => $movements[2]->customer_id,
        'amount' => $movements[2]->amount
    ]);
});

it('makes a withdrawal', function () {
    $customer = Customer::factory()->create();
    Movement::factory()->create([
        'customer_id' => $customer->id,
        'amount' => '100',
    ]);
    $movement = Movement::factory()->make([
        'customer_id' => $customer->id,
        'amount' => '-10',
    ]);

    $response = $this->post('/api/movements', $movement->toArray());

    $response->assertStatus(201);
    $this->assertDatabaseHas('movements', $movement->toArray());
});

it('makes a withdrawal over limit', function () {
    $customer = Customer::factory()->create();
    Movement::factory()->create([
        'customer_id' => $customer->id,
        'amount' => '90',
    ]);
    $movement = Movement::factory()->make([
        'customer_id' => $customer->id,
        'amount' => '-110',
    ]);

    $response = $this->post('/api/movements', $movement->toArray());

    $response->assertStatus(400);
    $this->assertDatabaseMissing('movements', $movement->toArray());
});

it('fails when trying to withdraw promotial money', function () {
    $amount = 100;
    $response = null;

    $customer = Customer::factory()->create();
    $withdrawAmount = (($amount * 3) + ($amount * ($customer->random_bonus / 100))) * -1;
    
    $movements = Movement::factory()->count(3)->make([
        'customer_id' => $customer->id,
        'amount' => $amount,
    ]);
    
    foreach ($movements as $movement) {
        $response = $this->post('/api/movements', $movement->toArray());
    }

    // dd($withdrawAmount);
    $movement = Movement::factory()->make([
        'customer_id' => $customer->id,
        'amount' => $withdrawAmount,
    ]);
    $response = $this->post('/api/movements', $movement->toArray());

    $response->assertStatus(400);
    $this->assertDatabaseMissing('movements', $movement->toArray());
});