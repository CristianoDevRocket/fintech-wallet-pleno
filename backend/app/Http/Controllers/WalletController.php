<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json(new WalletResource($request->user()->wallet));
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        $transaction = $this->walletService->deposit(
            $request->user()->wallet,
            number_format((float) $request->validated('amount'), 2, '.', '')
        );

        return response()->json(new TransactionResource($transaction), 201);
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        try {
            $transaction = $this->walletService->withdraw(
                $request->user()->wallet,
                number_format((float) $request->validated('amount'), 2, '.', '')
            );
        } catch (InsufficientBalanceException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(new TransactionResource($transaction), 201);
    }
}
