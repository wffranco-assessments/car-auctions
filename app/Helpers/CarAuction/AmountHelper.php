<?php

namespace App\Helpers\CarAuction;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * This class allow us to calculate auction fees for cars,
 * using the bid amount.
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
class AmountHelper implements Arrayable
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

    protected static function addBasicFee(float $value): float
    {
        return round($value + static::calculateBasicFee($value), 2);
    }

    protected static function addFixedFee(float $value): float
    {
        return round($value + static::calculateFixedFee($value), 2);
    }

    /**
     * Association fee depends of the amount:
     * - 5$ if the amount is between 1 and 500
     * - 10$ if the amount is greater than 500 up to 1000
     * - 15$ if the amount is greater than 1000 up to 3000
     * - 20$ if the amount is greater than 3000
     */
    protected static function calculateAssociationFee(float $amount): float
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
    protected static function calculateBasicFee(float $amount): float
    {
        if ($amount <= 0) return 0;
        return round(min(max($amount * static::BASIC_FEE_RATIO, static::MIN_BASIC_FEE), static::MAX_BASIC_FEE), 2);
    }

    /**
     * A fixed special fee for cars
     */
    protected static function calculateFixedFee(float $amount): float
    {
        return round($amount * static::FIXED_FEE_RATIO, 2);
    }

    /**
     * Storage fee.
     * Should be zero if no amount
     */
    protected static function calculateStorageFee(float $amount): float
    {
        return $amount > 0 ? static::STORAGE_FEE : 0;
    }
}
