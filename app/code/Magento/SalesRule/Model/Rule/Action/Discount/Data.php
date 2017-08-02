<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

/**
 * @api
 * @since 2.0.0
 */
class Data
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
    protected $originalAmount;

    /**
     * @var float
     * @since 2.0.0
     */
    protected $baseOriginalAmount;

    /**
     * Constructor
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->setAmount(0);
        $this->setBaseAmount(0);
        $this->setOriginalAmount(0);
        $this->setBaseOriginalAmount(0);
    }

    /**
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return float
     * @since 2.0.0
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $baseAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseAmount($baseAmount)
    {
        $this->baseAmount = $baseAmount;
        return $this;
    }

    /**
     * @return float
     * @since 2.0.0
     */
    public function getBaseAmount()
    {
        return $this->baseAmount;
    }

    /**
     * @param float $originalAmount
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getOriginalAmount()
    {
        return $this->originalAmount;
    }

    /**
     * @param float $baseOriginalAmount
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBaseOriginalAmount()
    {
        return $this->baseOriginalAmount;
    }
}
