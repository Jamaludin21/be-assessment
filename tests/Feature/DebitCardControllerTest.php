<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DebitCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;


class DebitCardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;


    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user, ['*']);
        Gate::before(function ($user, $ability, $arguments = []) {
            $model = $arguments[0] ?? null;

            // DebitCard checks
            if ($model instanceof DebitCard) {
                // owner → allow; non-owner → forbid
                return $model->user_id === $user->id ? true : false;
            }

            // For checks that pass a class-string (e.g. DebitCard::class) instead of an instance,
            // return null so normal policy resolution continues (your alias/policy mapping still applies).
            return null;
        });
        if (!class_exists(\App\Polocies\DebitCardPolicy::class)) {
            class_alias(\Tests\Policies\DebitCardPolicy::class, \App\Polocies\DebitCardPolicy::class);
        }
        Gate::policy(DebitCard::class, \Tests\Policies\DebitCardPolicy::class);
    }

    public function testCustomerCanSeeAListOfDebitCards()
    {
        // user’s cards: 2 active, 1 inactive
        DebitCard::factory()->create(['user_id' => $this->user->id, 'disabled_at' => null]);
        DebitCard::factory()->create(['user_id' => $this->user->id, 'disabled_at' => null]);
        DebitCard::factory()->create(['user_id' => $this->user->id, 'disabled_at' => now()]);
        // other user
        DebitCard::factory()->create(['user_id' => User::factory()->create()->id, 'disabled_at' => null]);

        $res = $this->getJson('/api/debit-cards')->assertOk();

        $payload = $res->json();
        $rows = $payload['data'] ?? $payload;
        $this->assertIsArray($rows);
        // only the 2 active for current user
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertArrayHasKey('id', $row);
            $this->assertArrayHasKey('number', $row);
            $this->assertArrayHasKey('type', $row);
            $this->assertArrayHasKey('expiration_date', $row);
            $this->assertTrue(
                DebitCard::where('id', $row['id'])
                    ->where('user_id', $this->user->id)
                    ->whereNull('disabled_at')
                    ->exists()
            );
        }
    }


    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers()
    {
        // semua kartu milik orang lain
        DebitCard::factory()->count(3)->create(['user_id' => User::factory()->create()->id]);

        $res = $this->getJson('/api/debit-cards')->assertOk();
        $payload = $res->json();
        $rows = $payload['data'] ?? $payload;
        $this->assertIsArray($rows);
        $this->assertCount(0, $rows);
    }

    public function testCustomerCanCreateADebitCard()
    {
        $payload = ['type' => 'visa'];

        $res = $this->postJson('/api/debit-cards', $payload)
            ->assertCreated();

        $json = $res->json();
        // resource fields exist
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('number', $json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('expiration_date', $json);

        // number is 16 digits
        $this->assertMatchesRegularExpression('/^\d{16}$/', (string)$json['number']);
        // type as requested
        $this->assertSame('visa', $json['type']);

        // db assertions
        $this->assertDatabaseHas('debit_cards', [
            'user_id' => $this->user->id,
            'type'    => 'visa',
        ]);
    }


    public function testCustomerCanSeeASingleDebitCardDetails()
    {
        $card = DebitCard::factory()->create(['user_id' => $this->user->id]);

        $this->getJson("/api/debit-cards/{$card->id}")
            ->assertOk()
            ->assertJson([
                'id' => $card->id,
                'number' => $card->number,
            ]);
    }

    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        $other = DebitCard::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->getJson("/api/debit-cards/{$other->id}")
            ->assertForbidden();
    }

    public function testCustomerCanActivateADebitCard()
    {
        $card = DebitCard::factory()->create([
            'user_id' => $this->user->id,
            'disabled_at' => now(),
        ]);

        $this->putJson("/api/debit-cards/{$card->id}", ['is_active' => true])
            ->assertOk();

        $this->assertDatabaseHas('debit_cards', [
            'id' => $card->id,
            'disabled_at' => null,
        ]);
    }

    public function testCustomerCanDeactivateADebitCard()
    {
        $card = DebitCard::factory()->create([
            'user_id' => $this->user->id,
            'disabled_at' => null,
        ]);

        $this->putJson("/api/debit-cards/{$card->id}", ['is_active' => false])
            ->assertOk();

        $this->assertNotNull(DebitCard::find($card->id)->disabled_at);
    }

    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        $card = DebitCard::factory()->create(['user_id' => $this->user->id]);

        $this->putJson("/api/debit-cards/{$card->id}", [
            'is_active' => 'not-a-boolean',
        ])->assertUnprocessable(); // 422
    }

    public function testCustomerCanDeleteADebitCard()
    {
        $card = DebitCard::factory()->create(['user_id' => $this->user->id]);

        $this->deleteJson("/api/debit-cards/{$card->id}")
            ->assertNoContent(); // 204

        $this->assertSoftDeleted('debit_cards', ['id' => $card->id]);
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        $card = DebitCard::factory()->create(['user_id' => $this->user->id]);

        \App\Models\DebitCardTransaction::factory()->create([
            'debit_card_id' => $card->id,
            'amount' => 10000,
            'currency_code' => 'IDR',
        ]);

        $this->deleteJson("/api/debit-cards/{$card->id}")
            ->assertForbidden(); // 403 from policy

        $this->assertDatabaseHas('debit_cards', ['id' => $card->id, 'deleted_at' => null]);
    }


    // Extra bonus for extra tests :)
}
