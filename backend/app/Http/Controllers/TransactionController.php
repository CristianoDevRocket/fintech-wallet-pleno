<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->wallet->transactions()->latest();

        if ($request->filled('type') && in_array($request->type, ['credit', 'debit'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $paginated = $query->paginate(15);

        return response()->json([
            'data'  => TransactionResource::collection($paginated->items()),
            'meta'  => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet;
        $now = now();

        $monthlyDeposited = $wallet->transactions()
            ->where('type', 'credit')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('amount');

        $monthlyWithdrawn = $wallet->transactions()
            ->where('type', 'debit')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('amount');

        $recentTransactions = $wallet->transactions()->latest()->take(5)->get();

        return response()->json([
            'wallet'               => new WalletResource($wallet),
            'monthly_deposited'    => number_format((float) $monthlyDeposited, 2, '.', ''),
            'monthly_withdrawn'    => number_format((float) $monthlyWithdrawn, 2, '.', ''),
            'recent_transactions'  => TransactionResource::collection($recentTransactions),
        ]);
    }
}
