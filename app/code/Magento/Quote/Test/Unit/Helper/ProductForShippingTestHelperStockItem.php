<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

/**
 * Minimal stock item helper with methods sometimes read by quote item flow.
 */
class ProductForShippingTestHelperStockItem
{
    /**
     * Whether qty is decimal.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsQtyDecimal()
    {
        return false;
    }

    /**
     * Qty increments value.
     *
     * @return int
     */
    public function getQtyIncrements()
    {
        return 1;
    }
}
