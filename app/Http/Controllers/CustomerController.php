<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
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
        
        $customer = new Customer;
        $customer = $customer->saveToDB($request, $customer);

        return response()->json([
            'status' => 'The customer was created successfully.',
            'results' => $customer
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        if (!$customer) {
            return response()->json([
                'status' => 'Customer not found.',
                'results' => $customer,
            ], 400);
        }

        if ($validation = $this->validateRequest($request, $customer)) {
            return $validation;
        }

        $customer = $customer->saveToDB($request, $customer);

        return response()->json([
            'status' => 'The customer was created successfully.',
            'results' => $customer
        ], 200);
    }

    protected function validateRequest(Request $request, Customer $customer = null)
    {
        $email = $customer ?
        'required|unique:customers,email,' . $customer->id :
        'required|unique:customers';

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => $email,
            'gender' => 'required',
            'country' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Some fields are not valid.',
                'results' => $validator->errors(),
            ], 400);
        }
    }
}
