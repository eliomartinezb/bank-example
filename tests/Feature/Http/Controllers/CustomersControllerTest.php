<?php

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('create customer correctly', function () {
    $customer = Customer::factory()->make();
    unset($customer->random_bonus)
    ;

    $response = $this->post('/api/customers', $customer->toArray());

    $response->assertStatus(201);
    $this->assertDatabaseHas('customers', $customer->toArray());
});

it('create customer empty fields', function () {
    $response = $this->post('/api/customers');
    $response->assertStatus(400);
});

it('create customer with email already in db', function () {
    $userEmail = Customer::factory()->create();
    $customer = Customer::factory()->make();
    $customer->email = $userEmail->email;

    $response = $this->post('/api/customers', $customer->toArray());

    $response->assertStatus(400);
    $this->assertDatabaseMissing('customers', $customer->toArray());
});

it('edits customer correctly', function () {
    $customer = Customer::factory()->create();
    $customer->first_name = 'first_name';

    $response = $this->put('/api/customers/' . $customer->id, $customer->toArray());

    $response->assertStatus(200);
    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'first_name' => $customer->first_name,
    ]);
});

it('edits customer empty fields', function () {
    $customer = Customer::factory()->create();
    $response = $this->put('/api/customers/' . $customer->id);
    $response->assertStatus(400);
});

it('edits customer should not change random_bonus', function () {
    $customer = Customer::factory()->create();
    $customer->first_name = 'first_name';
    $bonus_old = $customer->random_bonus;
    unset($customer->random_bonus);

    $response = $this->put('/api/customers/' . $customer->id, $customer->toArray());

    $this->assertEquals($response['results']['random_bonus'], $bonus_old);
});