<?php

namespace App\Http\Controllers\Api\Transaction;

use Illuminate\Http\Request;
use App\Models\UserTransaction;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserTransactionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function create(Request $request)
    {
       $validatedData =  $request->validate([
            'amount' => 'required|numeric',
            'transaction_id' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $validatedData['user_id'] = auth('api')->user()->id;
            $check = $this->checkTransaction($validatedData['transaction_id']);
            if(!is_null($check))
            {
                return response()->json([
                    'status' => false,
                    'message' => 'This transaction id has already been used',
                ], 400);
            }
            $userTransaction = UserTransaction::create($validatedData);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Transaction created successfully',
                'data' => $userTransaction,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkTransaction($transaction_id)
    {
        return UserTransaction::where('transaction_id', $transaction_id)->first();
    }

    public function list()
    {
        $user = auth('api')->user();

        if($user->role == "admin")
        {
            $transactions = UserTransaction::paginate(10);
        }else{
            $transactions = UserTransaction::where('user_id', $user->id)->paginate(10);
        }

        return response()->json([
            'status' => true,
            'data' => $transactions,
        ]);
    }

    public function updateTransactionStatus(Request $request, $transaction_id)
    {
        $validatedData =  $request->validate([
            'status' => 'required',
        ]);

        DB::beginTransaction();
        try {
                if(auth('api')->user()->role != "admin")
                {
                    return response()->json([
                        'status' => false,
                        'message' => 'You dont have the correct permission',
                      
                    ], 400);
                }

                $transaction = UserTransaction::findOrFail($transaction_id);
                $transaction->status = $validatedData['status'];
                $transaction->save();

                $user = User::findOrFail($transaction->user_id);

                // Update the user's wallet balance
                if ($validatedData['status'] == 'completed') {
                    $user->wallet_amount += $transaction->amount;
                    $user->save();
                }
                
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Transaction status updated successfully',
                    'data' => $transaction->refresh(),
                ], 201);     
        } catch (\Exception $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update transaction status',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

}
