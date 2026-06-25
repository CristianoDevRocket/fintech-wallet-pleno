<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithWallet(float $balance = 0): User
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => number_format($balance, 2, '.', '')]);
        return $user;
    }

    public function test_user_receives_wallet_on_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'João Silva',
            'email'                 => 'joao@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wallets', [
            'balance' => '0.00',
        ]);
        $this->assertDatabaseCount('wallets', 1);
    }

    public function test_user_can_deposit(): void
    {
        $user = $this->createUserWithWallet(0);

        $response = $this->actingAs($user)->postJson('/api/wallet/deposit', [
            'amount' => 100.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['type' => 'credit', 'amount' => '100.00']);

        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'balance' => '100.00']);
    }

    public function test_user_can_withdraw_with_sufficient_balance(): void
    {
        $user = $this->createUserWithWallet(200.00);

        $response = $this->actingAs($user)->postJson('/api/wallet/withdraw', [
            'amount' => 50.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['type' => 'debit', 'amount' => '50.00', 'balance_after' => '150.00']);

        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'balance' => '150.00']);
    }

    public function test_withdraw_fails_when_balance_is_insufficient(): void
    {
        $user = $this->createUserWithWallet(30.00);

        $response = $this->actingAs($user)->postJson('/api/wallet/withdraw', [
            'amount' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Saldo insuficiente para realizar o saque.']);

        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'balance' => '30.00']);
    }

    public function test_deposit_requires_positive_amount(): void
    {
        $user = $this->createUserWithWallet(0);

        $this->actingAs($user)->postJson('/api/wallet/deposit', ['amount' => 0])
            ->assertStatus(422);

        $this->actingAs($user)->postJson('/api/wallet/deposit', ['amount' => -10])
            ->assertStatus(422);
    }

    public function test_withdraw_requires_minimum_of_one_cent(): void
    {
        $user = $this->createUserWithWallet(100.00);

        $this->actingAs($user)->postJson('/api/wallet/withdraw', ['amount' => 0.001])
            ->assertStatus(422);
    }

    public function test_transaction_history_is_paginated_and_filterable(): void
    {
        $user = $this->createUserWithWallet(500.00);

        $this->actingAs($user)->postJson('/api/wallet/deposit', ['amount' => 100]);
        $this->actingAs($user)->postJson('/api/wallet/withdraw', ['amount' => 50]);

        $response = $this->actingAs($user)->getJson('/api/transactions');
        $response->assertStatus(200)->assertJsonStructure(['data', 'meta']);

        $creditOnly = $this->actingAs($user)->getJson('/api/transactions?type=credit');
        $creditOnly->assertStatus(200);
        $this->assertCount(1, $creditOnly->json('data'));
    }

    public function test_dashboard_returns_balance_and_monthly_summary(): void
    {
        $user = $this->createUserWithWallet(0);

        $this->actingAs($user)->postJson('/api/wallet/deposit', ['amount' => 300]);
        $this->actingAs($user)->postJson('/api/wallet/withdraw', ['amount' => 100]);

        $response = $this->actingAs($user)->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure(['wallet', 'monthly_deposited', 'monthly_withdrawn', 'recent_transactions'])
            ->assertJsonFragment(['monthly_deposited' => '300.00', 'monthly_withdrawn' => '100.00']);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->getJson('/api/wallet')->assertStatus(401);
        $this->postJson('/api/wallet/deposit', ['amount' => 100])->assertStatus(401);
        $this->postJson('/api/wallet/withdraw', ['amount' => 50])->assertStatus(401);
        $this->getJson('/api/transactions')->assertStatus(401);
        $this->getJson('/api/dashboard')->assertStatus(401);
    }
}
