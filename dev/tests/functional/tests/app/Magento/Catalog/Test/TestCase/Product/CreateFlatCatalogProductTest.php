<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Config\Test\TestStep\SetupConfigurationStep;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Preconditions:
 * 1. Use Flat Catalog Category & Use Flat Catalog Product are enabled (Store/Configuration/Catalog/Catalog/Storefront)
 *
 * Steps:
 * 1. Assign 16 or more products to the same category (e.g. 20 products)
 * 2. Go to Storefront and navigate to this category
 * 3. Click on Page 2 or any further page
 * 4. Go back to page 1 and change № of products per page from 9 to any number (e.g 12)
 * 5. Click on Page 2 or any further page
 * 5. Perform assertions.
 *
 * @ZephyrId MAGETWO-67570
 */
class CreateFlatCatalogProductTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Configuration data
     *
     * @var string
     */
    private $configData;

    /**
     * Factory for Fixtures
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Category fixture
     *
     * @var Category
     */
    private $category;

    /**
     * CatalogCategoryView page
     *
     * @var CatalogCategoryView
     */
    private $catalogCategoryView;

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
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    public function __inject(
        Category $category,
        FixtureFactory $fixtureFactory,
        CatalogCategoryView $catalogCategoryView
    ) {
        $this->category = $category;
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogCategoryView = $catalogCategoryView;
    }

    /**
     * Run create flat catalog product
     *
     * @param string $configData
     * @param string $productsCount
     * @return array
     */
    public function test($configData, $productsCount)
    {
        $this->objectManager->create(SetupConfigurationStep::class, ['configData' => $this->configData])->run();
        $this->createBulkOfProducts($productsCount);
        $this->configData = $configData;
        return ['category' => $this->category, 'catalogCategoryView' => $this->catalogCategoryView];
    }

    /**
     * Clear data after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }

    /**
     * Create products for tests
     *
     * @param $productsCount
     * @return void
     */
    private function createBulkOfProducts($productsCount)
    {
        for ($counter = 1; $counter <= $productsCount; $counter++) {
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
