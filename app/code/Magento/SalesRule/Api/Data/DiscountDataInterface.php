<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Api\Data;

/**
 * @api
 */
interface DiscountDataInterface
{
    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Set Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get Base Amount
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Set Base Amount
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);

    /**
     * Get Original Amount
     *
     * @return float
     */
    public function getOriginalAmount();

    /**
     * Set original Amount
     *
     * @param float $originalAmount
     * @return $this
     */
    public function setOriginalAmount($originalAmount);

    /**
     * Get Base Original Amount
     *
     * @return float
     */
    public function getBaseOriginalAmount();

    /**
     * Set base original Amount
     *
     * @param float $baseOriginalAmount
     * @return $this
     */
    public function setBaseOriginalAmount($baseOriginalAmount);
}
