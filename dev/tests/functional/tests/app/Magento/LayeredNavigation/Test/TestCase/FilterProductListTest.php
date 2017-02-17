<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Setup Layered Navigation configuration.
 *
 * Steps:
 * 1. Create category.
 * 2. Create product with created category.
 * 3. Perform all assertions.
 *
 * @group Layered_Navigation
 * @ZephyrId MAGETWO-12419, MAGETWO-30617, MAGETWO-30700, MAGETWO-30702, MAGETWO-30703
 */
class FilterProductListTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    /* end tags */

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Filtering product in the Frontend via layered navigation.
     *
     * @param string $configData
     * @param Category $category
     * @return array
     */
    public function test($configData, Category $category)
    {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Steps
        $category->persist();
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
