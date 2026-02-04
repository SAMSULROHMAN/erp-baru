<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $product;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->category = Category::factory()->create([
            'name' => 'Electronics',
            'description' => 'Electronic products'
        ]);

        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'code' => 'PROD-001',
            'name' => 'Test Product',
            'stock_quantity' => 100,
            'reorder_level' => 20,
        ]);
    }

    public function test_can_list_products()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'data' => [
                        'data' => [
                            '*' => ['id', 'code', 'name', 'stock_quantity']
                        ]
                    ]
                ]);
    }

    public function test_can_create_product()
    {
        $productData = [
            'code' => 'PROD-002',
            'name' => 'New Product',
            'category_id' => $this->category->id,
            'cost_price' => 10000,
            'selling_price' => 15000,
            'stock_quantity' => 50,
            'reorder_level' => 10,
            'unit' => 'pcs',
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.code', 'PROD-002');

        $this->assertDatabaseHas('products', [
            'code' => 'PROD-002'
        ]);
    }

    public function test_cannot_create_product_with_duplicate_code()
    {
        $productData = [
            'code' => $this->product->code,
            'name' => 'Duplicate Product',
            'category_id' => $this->category->id,
            'cost_price' => 10000,
            'selling_price' => 15000,
            'stock_quantity' => 50,
            'reorder_level' => 10,
            'unit' => 'pcs',
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422);
    }

    public function test_can_show_product()
    {
        $response = $this->getJson("/api/products/{$this->product->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.id', $this->product->id);
    }

    public function test_can_update_product()
    {
        $updateData = [
            'name' => 'Updated Product Name',
            'cost_price' => 12000,
        ];

        $response = $this->putJson("/api/products/{$this->product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'name' => 'Updated Product Name'
        ]);
    }

    public function test_can_delete_product()
    {
        $productId = $this->product->id;

        $response = $this->deleteJson("/api/products/{$productId}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_can_get_categories()
    {
        $response = $this->getJson('/api/products/categories');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_category()
    {
        $categoryData = [
            'name' => 'Furniture',
            'description' => 'Furniture products'
        ];

        $response = $this->postJson('/api/products/create-category', $categoryData);

        $response->assertStatus(201)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Furniture'
        ]);
    }

    public function test_can_get_low_stock_products()
    {
        // Create a low stock product
        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
            'reorder_level' => 20,
        ]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_product_is_low_stock()
    {
        $lowStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
            'reorder_level' => 20,
        ]);

        $this->assertTrue($lowStockProduct->isLowStock());
    }

    public function test_product_is_not_low_stock()
    {
        $this->assertFalse($this->product->isLowStock());
    }
}
