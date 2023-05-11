<?php

namespace Tests\Unit;

use App\Helpers\CarAuctionHelper;
use PHPUnit\Framework\TestCase;

class CarAuctionHelperTest extends TestCase
{
    protected function calculate(float $amount, float $fixed_fee, float $basic_fee, float $association_fee, float $storage_fee)
    {
        # Calculate
        $auction = new CarAuctionHelper($amount);

        $this->assertEquals($auction->toArray(), [
            'amount' => $amount,
            'basic_fee' => $basic_fee,
            'fixed_fee' => $fixed_fee,
            'association_fee' => $association_fee,
            'storage_fee' => $storage_fee,
            'total' => $amount + $basic_fee + $fixed_fee + $association_fee + $storage_fee,
        ]);
    }

    protected function reverse(float $bundle, float $amount)
    {
        # Calculate
        $bid = CarAuctionHelper::calculateAmountFromBudget($bundle);

        $this->assertEquals($bid, $amount);
    }

    /** @test */
    public function can_calculate_auctions_under_1()
    {
        $this->calculate(0, 0, 0, 0, 0);
        $this->calculate(0.01, 0, 10, 0, 100);
        $this->calculate(0.99, 0.02, 10, 0, 100);
    }

    /** @test */
    public function can_calculate_auctions_from_1_to_500()
    {
        $this->calculate(1, 0.02, 10, 5, 100);
        $this->calculate(100, 2, 10, 5, 100);

        # variable basic_fee
        $this->calculate(100.05, 2, 10.01, 5, 100);
        $this->calculate(300, 6, 30, 5, 100);
        $this->calculate(499.94, 10, 49.99, 5, 100);

        $this->calculate(500, 10, 50, 5, 100);
    }

    /** @test */
    public function can_calculate_auctions_from_500_to_1000()
    {
        $this->calculate(500.01, 10, 50, 10, 100);
        $this->calculate(750, 15, 50, 10, 100);
        $this->calculate(1000, 20, 50, 10, 100);
    }

    /** @test */
    public function can_calculate_auctions_from_1000_to_3000()
    {
        $this->calculate(1000.01, 20, 50, 15, 100);
        $this->calculate(2000, 40, 50, 15, 100);
        $this->calculate(3000, 60, 50, 15, 100);
    }

    /** @test */
    public function can_calculate_auctions_over_3000()
    {
        $this->calculate(3000.01, 60, 50, 20, 100);
        $this->calculate(5000, 100, 50, 20, 100);
        $this->calculate(10000, 200, 50, 20, 100);
    }

    /** @test */
    public function can_calculate_amount_from_bundle_under_1()
    {
        $this->reverse(0, 0);
        $this->reverse(110, 0);
        $this->reverse(110.01, 0.01);
        $this->reverse(111.01, 0.99);
        $this->reverse(116.01, 0.99);
    }

    /** @test */
    public function can_calculate_amount_from_bundle_from_1_to_500()
    {
        $this->reverse(116.02, 1);
        $this->reverse(217, 100);
        $this->reverse(217.06, 100.05);
        $this->reverse(441, 300);
        $this->reverse(664.93, 499.94);
        $this->reverse(665, 500);
        $this->reverse(670, 500);
    }

    /** @test */
    public function can_calculate_amount_from_bundle_from_500_to_1000()
    {
        $this->reverse(670.01, 500.01);
        $this->reverse(925, 750);
        $this->reverse(1180, 1000);
        $this->reverse(1185, 1000);
    }

    /** @test */
    public function can_calculate_amount_from_bundle_from_1000_to_3000()
    {
        $this->reverse(1185.01, 1000.01);
        $this->reverse(2205, 2000);
        $this->reverse(3225, 3000);
        $this->reverse(3230, 3000);
    }

    /** @test */
    public function can_calculate_amount_from_bundle_over_3000()
    {
        $this->reverse(3230.01, 3000.01);
        $this->reverse(3740, 3500);
    }
}
