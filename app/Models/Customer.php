<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Customer extends Model
{
    use HasFactory;

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function saveToDB(Request $request, Customer $customer = null)
    {
        if (!$customer->random_bonus) {
            $customer->random_bonus = rand(5,20);
        }
        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;
        $customer->email = $request->email;
        $customer->gender = $request->gender;
        $customer->country = $request->country;
        $customer->save();

        return $customer;
    }
}
