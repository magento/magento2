<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

/**
 * Minimal extension attributes carrying stock item for tests.
 */
class ProductForShippingTestHelperExtensionAttributes
{
    /**
     * Return minimal stock item helper.
     *
     * @return ProductForShippingTestHelperStockItem
     */
    public function getStockItem()
    {
        return new ProductForShippingTestHelperStockItem();
    }
}
