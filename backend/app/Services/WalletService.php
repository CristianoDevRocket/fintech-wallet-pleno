<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function deposit(Wallet $wallet, string $amount): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            $newBalance = bcadd($wallet->balance, $amount, 2);
            $wallet->update(['balance' => $newBalance]);

            return $wallet->transactions()->create([
                'type'         => 'credit',
                'amount'       => $amount,
                'balance_after' => $newBalance,
            ]);
        });
    }

    public function withdraw(Wallet $wallet, string $amount): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            if (bccomp($wallet->balance, $amount, 2) < 0) {
                throw new InsufficientBalanceException();
            }

            $newBalance = bcsub($wallet->balance, $amount, 2);
            $wallet->update(['balance' => $newBalance]);

            return $wallet->transactions()->create([
                'type'         => 'debit',
                'amount'       => $amount,
                'balance_after' => $newBalance,
            ]);
        });
    }
}
