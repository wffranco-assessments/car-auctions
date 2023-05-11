<?php

namespace App\Helpers\CarAuction;

/**
 * This class allow us to calculate auction fees for cars,
 * calculated from the final budget.
 *
 * All values are rounded to 2 decimals.
 *
 * @property float $budget  Maximum amount we want to spend in total.
 */
class BudgetHelper extends AmountHelper
{
    public function __construct(protected float $budget = 0)
    {
        $this->budget = round($budget, 2);
        $this->amount = static::calculateAmountFromBudget($this->budget);
    }

    public function getBudget()
    {
        return $this->budget;
    }

    public function toArray()
    {
        return ['budget' => $this->budget] + parent::toArray();
    }

    /**
     * Calculate amount from budget
     */
    protected static function calculateAmountFromBudget(float|int $budget): float
    {
        $budget = round($budget, 2);

        if ($budget < static::addBasicAndFixedFee(0.01) + static::STORAGE_FEE) return 0;

        $amount = static::reverseStorageFee($budget); # Substract storage fee

        $amount = static::reverseAssociationFee($amount); # Substract association fee

        $amount = static::reverseBasicAndFixedFees($amount); # Substract basic fee and fixed fee

        return round($amount, 2);
    }

    /**
     * Remove association fee from amount, asuming this contains basic and fixed fees too.
     */
    protected static function reverseAssociationFee(float $amount): float
    {
        if ($amount > static::addBasicAndFixedFee(3000) + 20) {
            return round($amount - 20, 2);
        }
        if ($amount > static::addBasicAndFixedFee(1000) + 15) {
            return min(round($amount - 15, 2), static::addBasicAndFixedFee(3000));
        }
        if ($amount > static::addBasicAndFixedFee(500) + 10) {
            return min(round($amount - 10, 2), static::addBasicAndFixedFee(1000));
        }
        if ($amount >= static::addBasicAndFixedFee(1) + 5) {
            return min(round($amount - 5, 2), static::addBasicAndFixedFee(500));
        }
        return min(round($amount, 2), static::addBasicAndFixedFee(0.99));
    }

    /**
     * Remove basic and fixed fees from amount.
     */
    protected static function reverseBasicAndFixedFees(float $amount): float
    {
        $min_basic_fee_value = round(static::MIN_BASIC_FEE + static::addFixedFee(static::MIN_BASIC_FEE) / static::BASIC_FEE_RATIO, 2);
        if ($amount <= $min_basic_fee_value) return ($amount - static::MIN_BASIC_FEE) / (1 + static::FIXED_FEE_RATIO);

        $max_basic_fee_value = round(static::MAX_BASIC_FEE + static::addFixedFee(static::MAX_BASIC_FEE) / static::BASIC_FEE_RATIO, 2);
        if ($amount >= $max_basic_fee_value) return ($amount - static::MAX_BASIC_FEE) / (1 + static::FIXED_FEE_RATIO);

        return round($amount / (1 + static::FIXED_FEE_RATIO + static::BASIC_FEE_RATIO), 2);
    }

    /**
     * Remove storage fee from amount, asuming this contains all fees.
     */
    protected static function reverseStorageFee(float $amount): float
    {
        if ($amount < static::addBasicAndFixedFee(0.01) + static::STORAGE_FEE) return round($amount, 2);
        return round($amount - static::STORAGE_FEE, 2);
    }
}
