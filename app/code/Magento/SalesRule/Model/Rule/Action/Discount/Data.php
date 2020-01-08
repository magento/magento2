<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

/**
 *  Discount Data
 *
 * @api
 * @since 100.0.2
 */
class Data
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
    protected $originalAmount;

    /**
     * @var float
     */
    protected $baseOriginalAmount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setAmount(0);
        $this->setBaseAmount(0);
        $this->setOriginalAmount(0);
        $this->setBaseOriginalAmount(0);
    }

    /**
     * Set Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set Base Amount
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount)
    {
        $this->baseAmount = $baseAmount;
        return $this;
    }

    /**
     * Get Base Amount
     *
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->baseAmount;
    }

    /**
     * Set Original Amount
     *
     * @param float $originalAmount
     * @return $this
     */
    public function setOriginalAmount($originalAmount)
    {
        $this->originalAmount = $originalAmount;
        return $this;
    }

    /**
     * Get discount for original price
     *
     * @return float
     */
    public function getOriginalAmount()
    {
        return $this->originalAmount;
    }

    /**
     * Set Base Original Amount
     *
     * @param float $baseOriginalAmount
     * @return $this
     */
    public function setBaseOriginalAmount($baseOriginalAmount)
    {
        $this->baseOriginalAmount = $baseOriginalAmount;
        return $this;
    }

    /**
     * Get discount for original price
     *
     * @return float
     */
    public function getBaseOriginalAmount()
    {
        return $this->baseOriginalAmount;
    }
}
