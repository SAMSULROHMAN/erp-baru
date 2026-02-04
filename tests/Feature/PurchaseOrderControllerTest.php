<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $supplier;
    protected $product;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->supplier = Supplier::factory()->create();

        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
    }

    public function test_can_list_purchase_orders()
    {
        $response = $this->getJson('/api/purchase-orders');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_purchase_order()
    {
        $poData = [
            'po_number' => 'PO-2024-001',
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(7)->toDateString(),
            'tax' => 100000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 50000,
                ]
            ]
        ];

        $response = $this->postJson('/api/purchase-orders', $poData);

        $response->assertStatus(201)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.po_number', 'PO-2024-001');

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2024-001',
            'status' => 'draft'
        ]);
    }

    public function test_can_show_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/purchase-orders/{$po->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_submit_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->patchJson("/api/purchase-orders/{$po->id}/submit");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'submitted'
        ]);
    }

    public function test_can_receive_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->user->id,
            'status' => 'submitted',
        ]);

        $poItem = \App\Models\PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 50000,
        ]);

        $receiveData = [
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'quantity' => 10,
                ]
            ],
            'delivery_date' => now()->toDateString(),
        ];

        $initialStock = $this->product->stock_quantity;
        $response = $this->patchJson("/api/purchase-orders/{$po->id}/receive", $receiveData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->product->refresh();
        $this->assertEquals($initialStock + 10, $this->product->stock_quantity);
    }

    public function test_can_cancel_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->patchJson("/api/purchase-orders/{$po->id}/cancel");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_can_delete_draft_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        \App\Models\PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $po->id,
        ]);

        $response = $this->deleteJson("/api/purchase-orders/{$po->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('purchase_orders', ['id' => $po->id]);
    }
}
