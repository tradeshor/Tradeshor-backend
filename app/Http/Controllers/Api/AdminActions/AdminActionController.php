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
            'amount' => 'nullable|numeric',
            'active_investment' => 'nullable|numeric',
            'earning' => 'nullable|numeric',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($request->active_investment) {
            $user->active_investment += $request->active_investment;
        }

        if ($request->earning) {
            $user->earning += $request->earning;
        }
        
        if ($request->amount) {
            $user->wallet_amount += $request->amount;
            $user->deposits += $request->amount;
        }
      
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
            'amount' => 'nullable|numeric',
            'active_investment' => 'nullable|numeric',
            'earning' => 'nullable|numeric',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($request->active_investment) {
            $user->active_investment -= $request->active_investment;
        }

        if ($request->earning) {
            $user->earning -= $request->earning;
        }

        if ($request->amount) {
            $user->wallet_amount -= $request->amount;
            $user->withdrawals += $request->amount;
        }
       
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
        $totl_investment = User::sum('active_investment');
        $totl_earning = User::sum('earning');
        $totl_deposits = User::sum('deposits');
        $total_withdrawals = User::sum('withdrawals');
        $total_transaction_amount = UserTransaction::sum('amount');

        return response()->json([
            'status' => true,
            'total_investment' => $totl_investment,
            'total_earning' => $totl_earning,
            'total_deposits' => $totl_deposits,
            'total_withdrawals' => $total_withdrawals,
            'total_users' => $total_users,
            'total_balance' => $total_balance,
            'total_transaction_amount' => $total_transaction_amount,
        ]);
    }

}
