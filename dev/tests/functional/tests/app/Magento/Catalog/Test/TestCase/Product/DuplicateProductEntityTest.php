<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Product is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS > Catalog.
 * 3. Click Product from grid.
 * 4. Click "Save & Duplicate".
 * 5. Perform asserts.
 *
 * @group Products
 * @ZephyrId MAGETWO-23294
 */
class DuplicateProductEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Category fixture.
     *
     * @var Category
     */
    protected $category;

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to update a product.
     *
     * @var CatalogProductEdit
     */
    protected $editProductPage;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepare data.
     *
     * @param Category $category
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(
        Category $category,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage,
        FixtureFactory $fixtureFactory
    ) {
        $this->category = $category;
        $this->category->persist();
        $this->productGrid = $productGrid;
        $this->editProductPage = $editProductPage;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Run test duplicate product entity.
     *
     * @param string $productType
     * @return array
     */
    public function test($productType)
    {
        // Precondition
        $product = $this->createProduct($productType);

        // Steps
        $filter = ['sku' => $product->getSku()];
        $this->productGrid->open();
        $this->productGrid->getProductGrid()->searchAndOpen($filter);
        $this->editProductPage->getFormPageActions()->saveAndDuplicate();

        return ['product' => $product];
    }

    /**
     * Creating a product according to the type of.
     *
     * @param string $productType
     * @return array
     */
    protected function createProduct($productType)
    {
        list($fixture, $dataset) = explode('::', $productType);
        $product = $this->fixtureFactory->createByCode(
            $fixture,
            [
                'dataset' => $dataset,
                'data' => [
                    'category_ids' => [
                        'category' => $this->category,
                    ],
                ]
            ]
        );
        $product->persist();

        return $product;
    }
}
