<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Api\Data;

/**
 * Discount Data Interface
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
     * Get Base Amount
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Get Original Amount
     *
     * @return float
     */
    public function getOriginalAmount();

    /**
     * Get Base Original Amount
     *
     * @return float
     */
    public function getBaseOriginalAmount();
}
