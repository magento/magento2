<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Create product.
 * 2. Perform all assertions.
 *
 * @group MAP_(MX)
 * @ZephyrId MAGETWO-12430, MAGETWO-12847
 */
class ApplyMapTest extends Injectable
{
    /* tags */
    const DOMAIN = 'MX';
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    /* end tags */

    /**
     * Apply minimum advertised price to product.
     *
     * @param string $product
     * @return array
     */
    public function test($product)
    {
        // Preconditions
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'msrp']
        )->run();
        $product = $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\CreateProductStep',
            ['product' => $product]
        )->run();

        return $product;
    }

    /**
     * Disable MAP on Config level.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'msrp', 'rollback' => true]
        )->run();
    }
}
