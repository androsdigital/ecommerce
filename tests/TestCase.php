<?php

namespace Tests;

use App\Models\Address;
use App\Models\Category;
use App\Models\City;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\State;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected ?State $state = null;

    protected ?City $city = null;

    protected ?Address $address = null;

    protected ?Category $category = null;

    protected ?Product $product = null;

    protected ?Size $size = null;

    protected ?Color $color = null;

    protected ?StockItem $stockItem = null;

    protected ?Order $order = null;

    protected ?Customer $customer = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    protected function createOrder(): void
    {
        $this->state = State::factory()->create();
        $this->city = City::factory()->create();
        $this->address = Address::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create();
        $this->size = Size::factory()->create();
        $this->color = Color::factory()->create();
        $this->stockItem = StockItem::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->order = Order::factory()->create();
    }

    protected function makeOrder(): Order
    {
        State::factory()->create();
        City::factory()->create();
        Address::factory()->create();
        Category::factory()->create();
        Product::factory()->create();
        Size::factory()->create();
        Color::factory()->create();
        StockItem::factory()->create();

        return Order::factory()->make();
    }
}
