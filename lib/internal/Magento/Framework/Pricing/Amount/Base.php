<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Amount;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;

/**
 * Amount base model
 * @since 2.0.0
 */
class Base implements AmountInterface
{
    /**
     * @var float
     * @since 2.0.0
     */
    protected $amount;

    /**
     * @var float
     * @since 2.0.0
     */
    protected $baseAmount;

    /**
     * @var float
     * @since 2.0.0
     */
    protected $totalAdjustmentAmount;

    /**
     * @var float[]
     * @since 2.0.0
     */
    protected $adjustmentAmounts = [];

    /**
     * @var AdjustmentInterface[]
     * @since 2.0.0
     */
    protected $adjustments = [];

    /**
     * @param float $amount
     * @param array $adjustmentAmounts
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

    /**
     * Return base amount part value
     *
     * @return float|null
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function hasAdjustment($adjustmentCode)
    {
        return array_key_exists($adjustmentCode, $this->adjustmentAmounts);
    }

    /**
     * Calculate base amount
     *
     * @return void
     * @since 2.0.0
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
