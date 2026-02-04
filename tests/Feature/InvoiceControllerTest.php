<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
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
        ]);
    }

    public function test_can_list_invoices()
    {
        $response = $this->getJson('/api/invoices');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_invoice()
    {
        $invoiceData = [
            'invoice_number' => 'INV-2024-001',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'tax' => 100000,
            'discount' => 50000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 100000,
                ]
            ]
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);

        $response->assertStatus(201)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-2024-001',
            'status' => 'draft'
        ]);
    }

    public function test_can_show_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_send_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->patchJson("/api/invoices/{$invoice->id}/send");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'sent'
        ]);
    }

    public function test_can_record_payment()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total' => 1000000,
            'amount_paid' => 0,
            'status' => 'sent',
        ]);

        $paymentData = [
            'amount' => 1000000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->patchJson("/api/invoices/{$invoice->id}/record-payment", $paymentData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $invoice->refresh();
        $this->assertEquals(1000000, $invoice->amount_paid);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_invoice_is_paid()
    {
        $invoice = Invoice::factory()->create([
            'total' => 1000000,
            'amount_paid' => 1000000,
        ]);

        $this->assertTrue($invoice->isPaid());
    }

    public function test_get_remaining_amount()
    {
        $invoice = Invoice::factory()->create([
            'total' => 1000000,
            'amount_paid' => 400000,
        ]);

        $remaining = $invoice->getRemainingAmount();

        $this->assertEquals(600000, $remaining);
    }

    public function test_cannot_record_payment_exceeding_remaining_balance()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total' => 1000000,
            'amount_paid' => 600000,
        ]);

        $paymentData = [
            'amount' => 500000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->patchJson("/api/invoices/{$invoice->id}/record-payment", $paymentData);

        $response->assertStatus(422)
                ->assertJson(['success' => false]);
    }

    public function test_can_delete_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
}
