<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\Category;

class CreateFlatCatalogProduct extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Category fixture
     *
     * @var Category
     */
    protected $category;

    /**
     * Prepare data
     *
     * @param Category $category
     * @return array
     */
    public function __prepare(Category $category)
    {
        $category->persist();
        return [
            'category' => $category
        ];
    }

    /**
     * Injection data
     *
     * @param Category $category
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        Category $category,
        FixtureFactory $fixtureFactory
    ) {
        $this->category = $category;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Run mass update product simple entity test.
     *
     * @param string $configData
     * @param string $productsCount
     * @return array
     */
    public function test($configData, $productsCount)
    {
        $this->createBulkOfProducts($productsCount);
        $this->configData = $configData;
        return ['category' => $this->category];
    }

    /**
     * Clear data after test.
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

    /**
     * @param $productsCount
     */
    private function createBulkOfProducts($productsCount)
    {
        foreach (range(1, $productsCount) as $element) {
            /**
             * @product FixtureInterface
             */
            $product = $this->fixtureFactory->createByCode(
                'catalogProductSimple',
                [
                    'dataset' => 'default',
                    'data' => [
                        'category_ids' => [
                            'category' => $this->category
                        ]
                    ]
                ]
            );
            $product->persist();
        }
    }
}
