<?php

namespace App\Http\Controllers;

use App\Helpers\CarAuctionHelper;
use Illuminate\Http\Request;

class CarAuctionController extends Controller
{
    public function index(Request $request)
    {
        $budget = +$request->budget;

        $amount = CarAuctionHelper::calculateAmountFromBudget($budget);

        $auction = new CarAuctionHelper($amount);

        return compact('budget') + $auction->toArray();
    }
}
