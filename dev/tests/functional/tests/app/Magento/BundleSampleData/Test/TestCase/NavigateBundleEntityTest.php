<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleSampleData\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * @ZephyrId MAGETWO-33559
 * @group Sample_Data_(MX)
 */
class NavigateBundleEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Run test navigate products
     *
     * @param BundleProduct $product
     * @return array
     */
    public function test(BundleProduct $product)
    {
        return ['product' => $product];
    }
}
