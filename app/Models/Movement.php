<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Movement extends Model
{
    use HasFactory;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function saveToDB(Request $request, Movement $movement, $promoAmount)
    {
        $movement->amount = round($request->amount, 2);
        $movement->customer_id = $request->customer_id;
        $movement->promo_amount = round($promoAmount, 2);
        $movement->save();
        return $movement;
    } 
}
