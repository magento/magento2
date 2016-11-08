<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\TestCase;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Bundle Product with options is created.
 *
 * Steps:
 * 1. Navigate to the Storefront Catalog Product Page.
 * 2. Select each bundle option and verify that Bundle Summary section updates with the option data.
 *
 * @group Bundle_Product
 * @ZephyrId MAGETWO-60637
 */
class BundleOptionsSummaryTest extends Injectable
{
    /**
     * Test bundle options summary block.
     *
     * @param BundleProduct $product
     * @return void
     */
    public function test(BundleProduct $product)
    {
        $product->persist();
    }
}
