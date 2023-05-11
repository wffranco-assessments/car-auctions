<?php

namespace App\Http\Controllers;

use App\Helpers\CarAuctionHelper;
use App\Http\Requests\CarAuctionRequest;
use Illuminate\Http\Request;

class CarAuctionController extends Controller
{
    public function index(CarAuctionRequest $request)
    {
        $budget = +$request->budget;

        $amount = CarAuctionHelper::calculateAmountFromBudget($budget);

        $auction = new CarAuctionHelper($amount);

        return compact('budget') + $auction->toArray();
    }
}
