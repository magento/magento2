<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

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
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount)
    {
        $this->baseAmount = $baseAmount;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->baseAmount;
    }

    /**
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
