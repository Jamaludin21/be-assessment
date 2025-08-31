<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DebitCardTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected DebitCard $debitCard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id
        ]);
        Passport::actingAs($this->user, ['*']);
        Gate::before(function ($user, $ability, $arguments = []) {
            $model = $arguments[0] ?? null;

            // DebitCard checks
            if ($model instanceof DebitCard) {
                // owner → allow; non-owner → forbid
                return $model->user_id === $user->id ? true : false;
            }

            // DebitCardTransaction checks
            if ($model instanceof DebitCardTransaction) {
                $card = $model->debitCard; // relies on relation
                return ($card && $card->user_id === $user->id) ? true : false;
            }

            // For checks that pass a class-string (e.g. DebitCard::class) instead of an instance,
            // return null so normal policy resolution continues (your alias/policy mapping still applies).
            return null;
        });
        if (!class_exists(\App\Polocies\DebitCardPolicy::class)) {
            class_alias(\Tests\Policies\DebitCardPolicy::class, \App\Polocies\DebitCardPolicy::class);
        }
        if (!class_exists(\App\Polocies\DebitCardTransactionPolicy::class)) {
            class_alias(\Tests\Policies\DebitCardTransactionPolicy::class, \App\Polocies\DebitCardTransactionPolicy::class);
        }

        // Optional but fine to keep:
        Gate::policy(DebitCard::class, \Tests\Policies\DebitCardPolicy::class);
        Gate::policy(DebitCardTransaction::class, \Tests\Policies\DebitCardTransactionPolicy::class);
    }

    public function testCustomerCanSeeAListOfDebitCardTransactions()
    {
        DebitCardTransaction::factory()->count(3)->create([
            'debit_card_id' => $this->debitCard->id,
        ]);
        // other user’s card + its transactions
        $otherCard = DebitCard::factory()->create(['user_id' => User::factory()->create()->id]);
        DebitCardTransaction::factory()->count(2)->create(['debit_card_id' => $otherCard->id]);

        $res = $this->getJson('/api/debit-card-transactions?debit_card_id=' . $this->debitCard->id)
            ->assertOk();

        $rows = $res->json()['data'] ?? $res->json();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        foreach ($rows as $row) {
            $this->assertSame($this->debitCard->id, $row['debit_card_id']);
        }
    }

    public function testCustomerCannotSeeAListOfDebitCardTransactionsOfOtherCustomerDebitCard()
    {
        $otherUser = User::factory()->create();
        $otherCard = DebitCard::factory()->create(['user_id' => $otherUser->id]);
        DebitCardTransaction::factory()->count(2)->create(['debit_card_id' => $otherCard->id]);

        // asking for someone else’s card should be blocked by request/policy
        $this->getJson('/api/debit-card-transactions?debit_card_id=' . $otherCard->id)
            ->assertForbidden();
    }

    public function testCustomerCanCreateADebitCardTransaction()
    {
        $payload = [
            'debit_card_id' => $this->debitCard->id,
            'amount' => 25000,
            'currency_code' => 'IDR',
        ];

        $this->postJson('/api/debit-card-transactions', $payload)
            ->assertCreated()
            ->assertJson([
                'debit_card_id' => $this->debitCard->id,
                'amount' => 25000,
                'currency_code' => 'IDR',
            ]);

        $this->assertDatabaseHas('debit_card_transactions', [
            'debit_card_id' => $this->debitCard->id,
            'amount' => 25000,
            'currency_code' => 'IDR',
        ]);
    }

    public function testCustomerCannotCreateADebitCardTransactionToOtherCustomerDebitCard()
    {
        $otherCard = DebitCard::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->postJson('/api/debit-card-transactions', [
            'debit_card_id' => $otherCard->id,
            'amount' => 5000,
            'currency_code' => 'IDR',
        ])->assertForbidden();

        $this->assertDatabaseMissing('debit_card_transactions', [
            'debit_card_id' => $otherCard->id,
            'amount' => 5000,
        ]);
    }

    public function testCustomerCanSeeADebitCardTransaction()
    {
        $trx = DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id,
            'amount' => 10000,
            'currency_code' => 'IDR',
        ]);

        $this->getJson("/api/debit-card-transactions/{$trx->id}")
            ->assertOk()
            ->assertJson([
                'id' => $trx->id,
                'debit_card_id' => $this->debitCard->id,
                'amount' => 10000,
            ]);
    }

    public function testCustomerCannotSeeADebitCardTransactionAttachedToOtherCustomerDebitCard()
    {
        $otherUser = User::factory()->create();
        $otherCard = DebitCard::factory()->create(['user_id' => $otherUser->id]);

        $trx = DebitCardTransaction::factory()->create([
            'debit_card_id' => $otherCard->id,
        ]);

        $this->getJson("/api/debit-card-transactions/{$trx->id}")
            ->assertForbidden();
    }

    // Extra bonus for extra tests :)
}
