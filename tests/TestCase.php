<?php

namespace Tests;

use App\Models\Address;
use App\Models\City;
use App\Models\Customer;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected State $state;

    protected City $city;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = State::factory()->create();
        $this->city = City::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->customer->addresses()->save(Address::factory()->create());

        $this->actingAs(User::factory()->create());
    }
}
