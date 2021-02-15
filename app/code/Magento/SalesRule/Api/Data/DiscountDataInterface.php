<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
