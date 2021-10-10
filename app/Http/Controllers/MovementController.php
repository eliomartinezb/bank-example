<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Movement;
use App\Models\Customer;
use DB;
use Illuminate\Http\Request;

class MovementController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($validation = $this->validateRequest($request)) {
            return $validation;
        }

        $movement = $this->processTransaction($request);

        if (!$movement) {
            return response()->json([
                'status' => 'Customer balance doesn\'t allow movements.',
                'results' => $request
            ], 400);
        }

        return response()->json([
            'status' => 'The movement was created successfully.',
            'results' => $movement
        ], 201);
    }

    protected function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Some fields are not valid.',
                'results' => $validator->errors(),
            ], 400);
        }
    }

    protected function processTransaction(Request $request)
    {
        return DB::transaction(function () use ($request) {
            if ($request->amount <= 0) {
                if (!$this->checkAmountAgainstBalance($request)){
                    return false;
                }
            }
            
            $promoAmount = 0;
            if ($request->amount > 0) {
                $promoAmount = $this->getBonusInMovement($request);
            }    
            
            $movement = new Movement;
            $movement = $movement->saveToDB($request, $movement, $promoAmount); 

            return $movement;
        });
    }

    protected function getBonusInMovement(Request $request)
    {
        $movementsCount = Movement::where('customer_id', $request->customer_id)->lockForUpdate()->count() + 1;
        if ($movementsCount % 3 == 0) {
            $customer = Customer::find($request->customer_id);
            return $request->amount * ($customer->random_bonus / 100);
        }
        return 0;
    }

    protected function checkAmountAgainstBalance(Request $request)
    {
        $customerBalance = Movement::where('customer_id', $request->customer_id)->lockForUpdate()->sum('amount');
        if ($customerBalance < ($request->amount * -1)) {
            return false;
        }
        return true;
    }

}
