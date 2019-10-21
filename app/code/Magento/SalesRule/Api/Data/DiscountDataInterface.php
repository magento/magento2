<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Api\Data;

interface DiscountDataInterface
{
    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Get Base Amount
     *
     * @return float
     */
    public function getBaseAmount(): float;

    /**
     * Get Original Amount
     *
     * @return float
     */
    public function getOriginalAmount(): float;

    /**
     * Get Base Original Amount
     *
     * @return float
     */
    public function getBaseOriginalAmount(): float;
}
