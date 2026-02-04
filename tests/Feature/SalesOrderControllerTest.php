<?php

namespace Tests\Feature;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $product;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->customer = Customer::factory()->create();

        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 100,
        ]);
    }

    public function test_can_list_sales_orders()
    {
        $response = $this->getJson('/api/sales-orders');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_sales_order()
    {
        $soData = [
            'so_number' => 'SO-2024-001',
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'required_date' => now()->addDays(7)->toDateString(),
            'tax' => 100000,
            'discount' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 50000,
                ]
            ]
        ];

        $response = $this->postJson('/api/sales-orders', $soData);

        $response->assertStatus(201)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.so_number', 'SO-2024-001');

        $this->assertDatabaseHas('sales_orders', [
            'so_number' => 'SO-2024-001',
            'status' => 'draft'
        ]);
    }

    public function test_can_show_sales_order()
    {
        $so = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/sales-orders/{$so->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_confirm_sales_order()
    {
        $so = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        \App\Models\SalesOrderItem::factory()->create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $response = $this->patchJson("/api/sales-orders/{$so->id}/confirm");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $so->id,
            'status' => 'confirmed'
        ]);
    }

    public function test_cannot_confirm_sales_order_with_insufficient_stock()
    {
        $lowStockProduct = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        $so = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        \App\Models\SalesOrderItem::factory()->create([
            'sales_order_id' => $so->id,
            'product_id' => $lowStockProduct->id,
            'quantity' => 10,
        ]);

        $response = $this->patchJson("/api/sales-orders/{$so->id}/confirm");

        $response->assertStatus(422)
                ->assertJson(['success' => false]);
    }

    public function test_can_ship_sales_order()
    {
        $so = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'confirmed',
        ]);

        $soItem = \App\Models\SalesOrderItem::factory()->create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'quantity_shipped' => 0,
        ]);

        $shipData = [
            'items' => [
                [
                    'sales_order_item_id' => $soItem->id,
                    'quantity' => 10,
                ]
            ],
            'shipped_date' => now()->toDateString(),
        ];

        $initialStock = $this->product->stock_quantity;
        $response = $this->patchJson("/api/sales-orders/{$so->id}/ship", $shipData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 10, $this->product->stock_quantity);
    }

    public function test_can_cancel_sales_order()
    {
        $so = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->patchJson("/api/sales-orders/{$so->id}/cancel");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $so->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_can_create_invoice_from_sales_order()
    {
        $so = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        \App\Models\SalesOrderItem::factory()->create([
            'sales_order_id' => $so->id,
        ]);

        $response = $this->postJson("/api/sales-orders/{$so->id}/create-invoice");

        $response->assertStatus(201)
                ->assertJson(['success' => true]);

        $this->assertTrue($so->invoice()->exists());
    }
}
