<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $supplier;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'email' => 'supplier@test.com',
        ]);
    }

    public function test_can_list_suppliers()
    {
        $response = $this->getJson('/api/suppliers');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_supplier()
    {
        $supplierData = [
            'name' => 'New Supplier',
            'email' => 'newsupplier@test.com',
            'phone' => '081234567890',
            'address' => 'Jl. Test 123',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
        ];

        $response = $this->postJson('/api/suppliers', $supplierData);

        $response->assertStatus(201)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.name', 'New Supplier');

        $this->assertDatabaseHas('suppliers', [
            'email' => 'newsupplier@test.com'
        ]);
    }

    public function test_can_show_supplier()
    {
        $response = $this->getJson("/api/suppliers/{$this->supplier->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_update_supplier()
    {
        $updateData = [
            'name' => 'Updated Supplier',
            'city' => 'Surabaya',
        ];

        $response = $this->putJson("/api/suppliers/{$this->supplier->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $this->supplier->id,
            'name' => 'Updated Supplier'
        ]);
    }

    public function test_can_delete_supplier()
    {
        $supplierId = $this->supplier->id;

        $response = $this->deleteJson("/api/suppliers/{$supplierId}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('suppliers', ['id' => $supplierId]);
    }

    public function test_cannot_delete_supplier_with_purchase_orders()
    {
        $supplier = Supplier::factory()->create();
        \App\Models\PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $response = $this->deleteJson("/api/suppliers/{$supplier->id}");

        $response->assertStatus(422)
                ->assertJson(['success' => false]);
    }
}
