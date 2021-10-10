<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lastDays = request('lastDays', 7);
        $movements = collect(Movement::where('created_at', '>=', Carbon::today()->subDays($lastDays))->with('customer')->get())->groupBy('customer.country');

        $response = $this->createResponse($movements);
        
        return response()->json([
            'status' => 'The customer was created successfully.',
            'results' => $response
        ], 200);
    }

    protected function createResponse($movements)
    {
        $response = array();

        foreach($movements as $key => $country) {
            $countryArray = array();
            $date = $country->map(function ($item) {
                return $item->created_at;
            })->sortDesc();
            $carbonDate = Carbon::parse($date[0]);
            $countryArray["date"] = $carbonDate->format('Y-m-d');
            $countryArray["country"] = $key;
            $countryArray["unique_customers"] = $country->map(function ($item) {
                return $item->customer;
            })->unique()->count();
            $countryArray["count_deposits"] = $country->where('amount', '>', 0)->count();
            $countryArray["total_deposits"] = $country->where('amount', '>', 0)->sum('amount');
            $countryArray["count_withdrawals"] = $country->where('amount', '<=', 0)->count();
            $countryArray["total_withdrawals"] = $country->where('amount', '<=', 0)->sum('amount');
            array_push($response, $countryArray);
        }

        return $response;
    }
}
