<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\Product;
use App\Models\Category;
use App\Models\BomItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $product;
    protected $materialProduct;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $category = Category::factory()->create();

        $this->product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->materialProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 1000,
        ]);

        // Create BOM
        BomItem::factory()->create([
            'product_id' => $this->product->id,
            'material_product_id' => $this->materialProduct->id,
            'quantity_required' => 2,
        ]);
    }

    public function test_can_list_production_orders()
    {
        $response = $this->getJson('/api/production-orders');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_production_order()
    {
        $proData = [
            'pro_number' => 'PRO-2024-001',
            'product_id' => $this->product->id,
            'quantity' => 50,
            'start_date' => now()->toDateString(),
            'scheduled_end_date' => now()->addDays(7)->toDateString(),
        ];

        $response = $this->postJson('/api/production-orders', $proData);

        $response->assertStatus(201)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('production_orders', [
            'pro_number' => 'PRO-2024-001',
            'status' => 'draft'
        ]);
    }

    public function test_cannot_create_production_order_without_bom()
    {
        $productWithoutBom = Product::factory()->create();

        $proData = [
            'pro_number' => 'PRO-2024-002',
            'product_id' => $productWithoutBom->id,
            'quantity' => 50,
            'start_date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/production-orders', $proData);

        $response->assertStatus(422)
                ->assertJson(['success' => false]);
    }

    public function test_can_show_production_order()
    {
        $pro = ProductionOrder::factory()->create([
            'product_id' => $this->product->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/production-orders/{$pro->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_schedule_production_order()
    {
        $pro = ProductionOrder::factory()->create([
            'product_id' => $this->product->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->patchJson("/api/production-orders/{$pro->id}/schedule");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('production_orders', [
            'id' => $pro->id,
            'status' => 'scheduled'
        ]);
    }

    public function test_can_start_production()
    {
        $pro = ProductionOrder::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 50,
            'created_by' => $this->user->id,
            'status' => 'scheduled',
        ]);

        $response = $this->patchJson("/api/production-orders/{$pro->id}/start");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('production_orders', [
            'id' => $pro->id,
            'status' => 'in_progress'
        ]);
    }

    public function test_can_report_production()
    {
        $pro = ProductionOrder::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 50,
            'quantity_produced' => 0,
            'created_by' => $this->user->id,
            'status' => 'in_progress',
        ]);

        $initialMaterialStock = $this->materialProduct->stock_quantity;
        $initialProductStock = $this->product->stock_quantity;

        $reportData = [
            'quantity_produced' => 10,
        ];

        $response = $this->patchJson("/api/production-orders/{$pro->id}/report-production", $reportData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->materialProduct->refresh();
        $this->product->refresh();

        // Material should be deducted (10 * 2 = 20 units)
        $this->assertEquals($initialMaterialStock - 20, $this->materialProduct->stock_quantity);
        // Finished product should be added
        $this->assertEquals($initialProductStock + 10, $this->product->stock_quantity);
    }

    public function test_production_order_shows_progress()
    {
        $pro = ProductionOrder::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 100,
            'quantity_produced' => 50,
        ]);

        $progress = $pro->getProgress();

        $this->assertEquals(50.0, $progress);
    }

    public function test_can_cancel_production_order()
    {
        $pro = ProductionOrder::factory()->create([
            'product_id' => $this->product->id,
            'created_by' => $this->user->id,
            'status' => 'scheduled',
        ]);

        $response = $this->patchJson("/api/production-orders/{$pro->id}/cancel");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('production_orders', [
            'id' => $pro->id,
            'status' => 'cancelled'
        ]);
    }
}
