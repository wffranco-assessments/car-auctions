<?php

namespace Tests\Unit\CarAuction;

use App\Helpers\CarAuction\AmountHelper;
use PHPUnit\Framework\TestCase;

class AmountHelperTest extends TestCase
{
    protected function calculate(float $amount, float $fixed_fee, float $basic_fee, float $association_fee, float $storage_fee)
    {
        # Calculate
        $auction = new AmountHelper($amount);

        $this->assertEquals($auction->toArray(), [
            'amount' => $amount,
            'basic_fee' => $basic_fee,
            'fixed_fee' => $fixed_fee,
            'association_fee' => $association_fee,
            'storage_fee' => $storage_fee,
            'total' => $amount + $basic_fee + $fixed_fee + $association_fee + $storage_fee,
        ]);
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
}
