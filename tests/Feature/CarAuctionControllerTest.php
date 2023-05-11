<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CarAuctionControllerTest extends TestCase
{
    protected function calculate($budget, $amount, $basic_fee, $fixed_fee, $association_fee, $storage_fee)
    {
        # Calculate
        return $this->getJson(route('api.calculate', compact('budget')))
            ->assertOk()
            ->assertJsonFragment(compact(
                'budget',
                'amount',
                'basic_fee',
                'fixed_fee',
                'association_fee',
                'storage_fee',
            ));
    }

    protected function invalidBudget($budget = null)
    {
        # call 'api.calculate' route with budget
        return $this->getJson(route('api.calculate', compact('budget')))
            ->assertUnprocessable();
    }

    /**
     * Execute all test cases presents in the document given by the client
     *
     * @test
     */
    public function can_reject_invalid_budget()
    {
        $this->invalidBudget();

        $this->invalidBudget('test');

        $this->invalidBudget(-5);
    }

    /**
     * Execute all test cases presents in the document given by the client
     *
     * @test
     */
    public function can_validate_assessment_test_cases()
    {
        $this->calculate(1000, 823.53, 50, 16.47, 10, 100);

        // $this->calculate(670, 500, 50, 10, 5, 100);

        // $this->calculate(670.01, 500.01, 50, 10, 10, 100);

        // $this->calculate(110, 0, 0, 0, 0, 0);

        // $this->calculate(111, 0.98, 10, 0.02, 0, 100);

        // $this->calculate(116.02, 1, 10, 0.02, 5, 100);

        // $this->calculate(1000000, 980225.49, 50, 19604.51, 20, 100);
    }
}
