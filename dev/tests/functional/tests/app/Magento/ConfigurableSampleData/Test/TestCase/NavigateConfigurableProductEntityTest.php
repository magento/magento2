<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableSampleData\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;

/**
 * @ZephyrId MAGETWO-33559
 * @group Sample_Data_(MX)
 */
class NavigateConfigurableProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Run test navigate products.
     *
     * @param ConfigurableProduct $product
     * @return array
     */
    public function test(ConfigurableProduct $product)
    {
        return ['product' => $product];
    }
}
