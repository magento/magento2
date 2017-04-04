<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Product is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Open Reports > Low Stock.
 * 3. Perform appropriate assertions.
 *
 * @group Reports
 * @ZephyrId MAGETWO-27193
 */
class LowStockProductsReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Configuration data
     *
     * @var string
     */
    private $configData;

    /**
     * Create product
     *
     * @param CatalogProductSimple $product
     * @param string|null $configData
     * @return void
     */
    public function test(CatalogProductSimple $product, $configData = null)
    {
        $this->configData = $configData;

        if ($this->configData) {
            $this->objectManager->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData, 'flushCache' => true]
            )->run();
        }

        // Preconditions
        $product->persist();
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->configData) {
            $this->objectManager->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData, 'rollback' => true, 'flushCache' => true]
            )->run();
        }
    }
}
