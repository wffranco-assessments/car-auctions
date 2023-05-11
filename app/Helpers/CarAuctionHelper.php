<?php

namespace App\Helpers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * This class allow us to calculate auction fees for cars,
 * using the bid amount or the max amount to bid calculated from the final budget.
 *
 * All values are rounded to 2 decimals.
 *
 * @property float $amount          amount to bid
 * @property float $basic_fee
 * @property float $fixed_fee       a special fee for cars
 * @property float $association_fee
 * @property float $storage_fee
 * @property float $total           total auction value
 */
class CarAuctionHelper implements Arrayable
{
    /** Basic fee is a 10% of the amount, but not less than $10 and no more than $50. */
    const BASIC_FEE_RATIO = 0.1;
    /** Min basic fee of $10 */
    const MIN_BASIC_FEE = 10;
    /** Max basic fee of $50 */
    const MAX_BASIC_FEE = 50;
    /** A fixed special fee for cars, is a 2% of the amount. */
    const FIXED_FEE_RATIO = 0.02;
    /** Storage fee is a fixed value of $100. */
    const STORAGE_FEE = 100;

    public function __construct(protected float $amount = 0)
    {
        $this->amount = round($amount, 2);
    }

    public function __get($name)
    {
        # method name in camel case (prefixed by `get`)
        $method = Str::camel("get_{$name}");

        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getBasicFee(): float
    {
        return static::calculateBasicFee($this->amount);
    }

    public function getFixedFee(): float
    {
        return static::calculateFixedFee($this->amount);
    }

    public function getAssociationFee(): float
    {
        return static::calculateAssociationFee($this->amount);
    }

    public function getStorageFee(): float
    {
        return static::calculateStorageFee($this->amount);
    }

    public function getTotal(): float
    {
        return $this->amount + $this->basic_fee + $this->fixed_fee + $this->association_fee + $this->storage_fee;
    }

    public function toArray()
    {
        # returns amount with all fees and a total
        return [
            'amount' => $this->amount,
            'basic_fee' => $this->basic_fee,
            'fixed_fee' => $this->fixed_fee,
            'association_fee' => $this->association_fee,
            'storage_fee' => $this->storage_fee,
            'total' => $this->total,
        ];
    }

    public static function addBasicFee(float $value): float
    {
        return round($value + static::calculateBasicFee($value), 2);
    }

    public static function addFixedFee(float $value): float
    {
        return round($value + static::calculateFixedFee($value), 2);
    }

    public static function addBasicAndFixedFee(float $value): float
    {
        return static::addBasicFee(static::addFixedFee($value));
    }

    /**
     * Calculate amount from budget
     */
    public static function calculateAmountFromBudget(float|int $budget): float
    {
        $budget = round($budget, 2);

        if ($budget < static::addBasicAndFixedFee(0.01) + static::STORAGE_FEE) return 0;

        $amount = static::reverseStorageFee($budget); # Substract storage fee

        $amount = static::reverseAssociationFee($amount); # Substract association fee

        $amount = static::reverseBasicAndFixedFees($amount); # Substract basic fee and fixed fee

        return round($amount, 2);
    }

    /**
     * Association fee depends of the amount:
     * - 5$ if the amount is between 1 and 500
     * - 10$ if the amount is greater than 500 up to 1000
     * - 15$ if the amount is greater than 1000 up to 3000
     * - 20$ if the amount is greater than 3000
     */
    public static function calculateAssociationFee(float $amount): float
    {
        if ($amount < 1) return 0;
        if ($amount <= 500) return 5;
        if ($amount <= 1000) return 10;
        if ($amount <= 3000) return 15;
        return 20;
    }

    /**
     * Calculate basic fee.
     * Should be zero if no amount.
     */
    public static function calculateBasicFee(float $amount): float
    {
        if ($amount <= 0) return 0;
        return round(min(max($amount * static::BASIC_FEE_RATIO, static::MIN_BASIC_FEE), static::MAX_BASIC_FEE), 2);
    }

    /**
     * A fixed special fee for cars
     */
    public static function calculateFixedFee(float $amount): float
    {
        return round($amount * static::FIXED_FEE_RATIO, 2);
    }

    /**
     * Storage fee.
     * Should be zero if no amount
     */
    public static function calculateStorageFee(float $amount): float
    {
        return $amount > 0 ? static::STORAGE_FEE : 0;
    }

    /**
     * Remove association fee from amount, asuming this contains basic and fixed fees too.
     */
    public static function reverseAssociationFee(float $amount): float
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
    public static function reverseBasicAndFixedFees(float $amount): float
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
    public static function reverseStorageFee(float $amount): float
    {
        if ($amount < static::addBasicAndFixedFee(0.01) + static::STORAGE_FEE) return round($amount, 2);
        return round($amount - static::STORAGE_FEE, 2);
    }
}
