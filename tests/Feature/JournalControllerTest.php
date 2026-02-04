<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $assetAccount;
    protected $liabilityAccount;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->assetAccount = ChartOfAccount::factory()->create([
            'code' => '1000',
            'name' => 'Cash',
            'type' => 'asset',
        ]);

        $this->liabilityAccount = ChartOfAccount::factory()->create([
            'code' => '2000',
            'name' => 'Accounts Payable',
            'type' => 'liability',
        ]);
    }

    public function test_can_list_journals()
    {
        $response = $this->getJson('/api/journals');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_balanced_journal()
    {
        $journalData = [
            'journal_number' => 'J-2024-001',
            'type' => 'general',
            'journal_date' => now()->toDateString(),
            'description' => 'Test Journal',
            'details' => [
                [
                    'chart_of_account_id' => $this->assetAccount->id,
                    'debit' => 1000000,
                    'credit' => 0,
                    'description' => 'Debit cash',
                ],
                [
                    'chart_of_account_id' => $this->liabilityAccount->id,
                    'debit' => 0,
                    'credit' => 1000000,
                    'description' => 'Credit payable',
                ]
            ]
        ];

        $response = $this->postJson('/api/journals', $journalData);

        $response->assertStatus(201)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('journals', [
            'journal_number' => 'J-2024-001',
            'status' => 'draft'
        ]);
    }

    public function test_cannot_create_unbalanced_journal()
    {
        $journalData = [
            'journal_number' => 'J-2024-002',
            'type' => 'general',
            'journal_date' => now()->toDateString(),
            'details' => [
                [
                    'chart_of_account_id' => $this->assetAccount->id,
                    'debit' => 1000000,
                    'credit' => 0,
                ],
                [
                    'chart_of_account_id' => $this->liabilityAccount->id,
                    'debit' => 0,
                    'credit' => 500000,
                ]
            ]
        ];

        $response = $this->postJson('/api/journals', $journalData);

        $response->assertStatus(422)
                ->assertJson(['success' => false]);
    }

    public function test_can_show_journal()
    {
        $journal = Journal::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/journals/{$journal->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_post_balanced_journal()
    {
        $journal = Journal::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        \App\Models\JournalDetail::factory()->create([
            'journal_id' => $journal->id,
            'chart_of_account_id' => $this->assetAccount->id,
            'debit' => 1000000,
            'credit' => 0,
        ]);

        \App\Models\JournalDetail::factory()->create([
            'journal_id' => $journal->id,
            'chart_of_account_id' => $this->liabilityAccount->id,
            'debit' => 0,
            'credit' => 1000000,
        ]);

        $response = $this->patchJson("/api/journals/{$journal->id}/post");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('journals', [
            'id' => $journal->id,
            'status' => 'posted'
        ]);
    }

    public function test_cannot_post_unbalanced_journal()
    {
        $journal = Journal::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        \App\Models\JournalDetail::factory()->create([
            'journal_id' => $journal->id,
            'chart_of_account_id' => $this->assetAccount->id,
            'debit' => 1000000,
            'credit' => 0,
        ]);

        $response = $this->patchJson("/api/journals/{$journal->id}/post");

        $response->assertStatus(422)
                ->assertJson(['success' => false]);
    }

    public function test_journal_is_balanced()
    {
        $journal = Journal::factory()->create();

        \App\Models\JournalDetail::factory()->create([
            'journal_id' => $journal->id,
            'debit' => 1000000,
            'credit' => 0,
        ]);

        \App\Models\JournalDetail::factory()->create([
            'journal_id' => $journal->id,
            'debit' => 0,
            'credit' => 1000000,
        ]);

        $this->assertTrue($journal->isBalanced());
    }

    public function test_can_delete_draft_journal()
    {
        $journal = Journal::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        \App\Models\JournalDetail::factory()->create([
            'journal_id' => $journal->id,
        ]);

        $response = $this->deleteJson("/api/journals/{$journal->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('journals', ['id' => $journal->id]);
    }
}
