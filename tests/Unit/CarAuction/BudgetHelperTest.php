<?php

namespace Tests\Unit\CarAuction;

use App\Helpers\CarAuction\BudgetHelper;
use PHPUnit\Framework\TestCase;

class BudgetHelperTest extends TestCase
{
    protected function calculate(float $budget, float $amount)
    {
        # Calculate
        $auction = new BudgetHelper($budget);

        $this->assertEquals($amount, $auction->amount);
    }

    /** @test */
    public function can_calculate_auctions_under_1()
    {
        $this->calculate(0, 0);
        $this->calculate(110, 0);
        $this->calculate(110.01, 0.01);
        $this->calculate(111.01, 0.99);
        $this->calculate(116.01, 0.99);
    }

    /** @test */
    public function can_calculate_auctions_from_1_to_500()
    {
        $this->calculate(116.02, 1);
        $this->calculate(217, 100);
        $this->calculate(217.06, 100.05);
        $this->calculate(441, 300);
        $this->calculate(664.93, 499.94);
        $this->calculate(665, 500);
        $this->calculate(670, 500);
    }

    /** @test */
    public function can_calculate_auctions_from_500_to_1000()
    {
        $this->calculate(670.01, 500.01);
        $this->calculate(925, 750);
        $this->calculate(1180, 1000);
        $this->calculate(1185, 1000);
    }

    /** @test */
    public function can_calculate_auctions_from_1000_to_3000()
    {
        $this->calculate(1185.01, 1000.01);
        $this->calculate(2205, 2000);
        $this->calculate(3225, 3000);
        $this->calculate(3230, 3000);
    }

    /** @test */
    public function can_calculate_auctions_over_3000()
    {
        $this->calculate(3230.01, 3000.01);
        $this->calculate(3740, 3500);
    }
}
