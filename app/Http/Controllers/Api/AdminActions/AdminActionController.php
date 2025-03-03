<?php

namespace App\Http\Controllers\Api\AdminActions;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Resources\UserTransformer;
use App\Http\Controllers\Controller;

class AdminActionController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->wallet_amount += $request->amount;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Deposit successful',
            'data' => $user,
        ], 200);
    }

    public function debit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->wallet_amount -= $request->amount;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Credit successful',
            'data' => $user,
        ], 200);
    }

    public function listUsers()
    {
        $users = User::whereNot('role', 'admin')->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $users,
        ]);
    }

    public function stats()
    {
        $total_users = User::count();
       
        $total_balance = User::sum('wallet_amount');

        $total_transaction_amount = UserTransaction::sum('amount');

        return response()->json([
            'status' => true,
            'total_users' => $total_users,
            'total_balance' => $total_balance,
            'total_transaction_amount' => $total_transaction_amount,
        ]);
    }

}
