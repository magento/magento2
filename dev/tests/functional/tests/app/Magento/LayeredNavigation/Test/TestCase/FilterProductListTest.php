<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Create category.
 * 2. Create product with created category.
 * 3. Perform all assertions.
 *
 * @group LayeredNavigation_(MX)
 * @ZephyrId MAGETWO-12419
 */
class FilterProductListTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Category fixture.
     *
     * @var Category
     */
    protected $category;

    /**
     * Filtering product in the Frontend via layered navigation.
     *
     * @param FixtureFactory $fixtureFactory
     * @param string $configData
     * @param Category $category
     * @param string $products
     * @return array
     */
    public function test(FixtureFactory $fixtureFactory, $configData, Category $category, $products)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->configData = $configData;
        $this->category = $category;

        // Preconditions
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();

        // Steps
        $this->category->persist();
        $products = $this->prepareProducts($products);

        return ['products' => $products];
    }

    /**
     * Create products and assign to category.
     *
     * @param string $products
     * @return FixtureInterface[]
     */
    public function prepareProducts($products)
    {
        $products = array_map('trim', explode(',', $products));
        $result = [];

        foreach ($products as $productData) {
            list($productCode, $dataSet) = explode('::', $productData);
            $product = $this->fixtureFactory->createByCode(
                $productCode,
                [
                    'dataSet' => $dataSet,
                    'data' => [
                        'category_ids' => [
                            'presets' => null,
                            'category' => $this->category
                        ]
                    ]
                ]
            );

            $product->persist();
            $result[] = $product;
        }

        return $result;
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
