<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Amount;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;

/**
 * Amount base model
 */
class Base implements AmountInterface
{
    /**
     * @var float
     */
    protected $amount;

    /**
     * @var float
     */
    protected $baseAmount;

    /**
     * @var float
     */
    protected $totalAdjustmentAmount;

    /**
     * @var float[]
     */
    protected $adjustmentAmounts = [];

    /**
     * @var AdjustmentInterface[]
     */
    protected $adjustments = [];

    /**
     * @param float $amount
     * @param array $adjustmentAmounts
     */
    public function __construct(
        $amount,
        array $adjustmentAmounts = []
    ) {
        $this->amount = $amount;
        $this->adjustmentAmounts = $adjustmentAmounts;
    }

    /**
     * Return full amount value
     *
     * @param null|string|array $exclude
     * @return float
     */
    public function getValue($exclude = null)
    {
        if ($exclude === null) {
            return $this->amount;
        } else {
            if (!is_array($exclude)) {
                $exclude = [(string)$exclude];
            }
            $amount = $this->amount;
            foreach ($exclude as $code) {
                if ($this->hasAdjustment($code)) {
                    $amount -= $this->adjustmentAmounts[$code];
                }
            }
            return $amount;
        }
    }

    /**
     * Return full amount value in string format
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

    /**
     * Return base amount part value
     *
     * @return float|null
     */
    public function getBaseAmount()
    {
        if ($this->baseAmount === null) {
            $this->calculateAmounts();
        }
        return $this->baseAmount;
    }

    /**
     * Return adjustment amount part value by adjustment code
     *
     * @param string $adjustmentCode
     * @return bool|float
     */
    public function getAdjustmentAmount($adjustmentCode)
    {
        return isset($this->adjustmentAmounts[$adjustmentCode])
            ? $this->adjustmentAmounts[$adjustmentCode]
            : false;
    }

    /**
     * Return sum amount of all applied adjustments
     *
     * @return float|null
     */
    public function getTotalAdjustmentAmount()
    {
        if ($this->totalAdjustmentAmount === null) {
            $this->calculateAmounts();
        }
        return $this->totalAdjustmentAmount;
    }

    /**
     * Return all applied adjustments as array
     *
     * @return float[]
     */
    public function getAdjustmentAmounts()
    {
        return $this->adjustmentAmounts;
    }

    /**
     * Check if adjustment is contained in amount object
     *
     * @param string $adjustmentCode
     * @return bool
     */
    public function hasAdjustment($adjustmentCode)
    {
        return array_key_exists($adjustmentCode, $this->adjustmentAmounts);
    }

    /**
     * Calculate base amount
     *
     * @return void
     */
    protected function calculateAmounts()
    {
        $this->baseAmount = $this->amount;
        $this->totalAdjustmentAmount = 0.;
        foreach ($this->adjustmentAmounts as $amount) {
            $this->baseAmount -= $amount;
            $this->totalAdjustmentAmount += $amount;
        }
    }
}
