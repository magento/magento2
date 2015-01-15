<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for LowStockProductsReportEntityTest
 *
 * Test Flow:
 * Preconditions:
 * 1. Product is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Open Reports > Low Stock.
 * 3. Perform appropriate assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27193
 */
class LowStockProductsReportEntityTest extends Injectable
{
    /**
     * Create product
     *
     * @param CatalogProductSimple $product
     * @return void
     */
    public function test(CatalogProductSimple $product)
    {
        // Preconditions
        $product->persist();
    }
}
