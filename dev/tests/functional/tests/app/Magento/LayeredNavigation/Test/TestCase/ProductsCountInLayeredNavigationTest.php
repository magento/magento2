<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Create four categories assigned in ascending order (Default Category->first->second->third->fourth)
 * first and third categories should not be anchored, second and fourth categories should be anchored
 * 2. Create configurable product with two configurable options and assign it to category "fourth"
 *
 * Steps:
 * 1. Disable configurable options via massaction or from edit product page
 * 2. Open created non anchored categories on frontend
 * 3. Perform assertions
 *
 * @group Layered_Navigation
 * @ZephyrId MAGETWO-82891
 */
class ProductsCountInLayeredNavigationTest extends Injectable
{
    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Page to update a product
     *
     * @var CatalogProductEdit
     */
    protected $editProductPage;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $editProductPage
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $editProductPage,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->editProductPage = $editProductPage;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test category name and products count displaying in layered navigation after configurable options disabling
     *
     * @param Category $category
     * @param boolean $disableFromProductsGreed
     * @return array
     */
    public function test(
        Category $category,
        $disableFromProductsGreed = true
    ) {
        // Preconditions
        $category->persist();
        // Steps
        $products = $category->getDataFieldConfig('category_products')['source']->getProducts();
        $configurableOptions = [];
        /** @var \Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct\ $product */
        foreach ($products as $product) {
            $configurableOptions = array_merge(
                $configurableOptions,
                $product->getConfigurableAttributesData()['matrix']
            );
        }
        // Disable configurable options
        if ($disableFromProductsGreed) {
            $this->catalogProductIndex->open();
            foreach ($configurableOptions as $configurableOption) {
                $filter = ['sku' => $configurableOption['sku']];
                $this->catalogProductIndex->getProductGrid()->search($filter);
                $this->catalogProductIndex->getProductGrid()->selectItems([$filter]);
                $this->catalogProductIndex->getProductGrid()->selectAction(['Change status' => 'Disable']);
            }
        } else {
            $productToDisable = $this->fixtureFactory->createByCode(
                'catalogProductSimple',
                ['data' => ['status' => 'No']]
            );
            foreach ($configurableOptions as $configurableOption) {
                $filter = ['sku' => $configurableOption['sku']];
                $this->catalogProductIndex->open();
                $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
                $this->editProductPage->getProductForm()->fill($productToDisable);
                $this->editProductPage->getFormPageActions()->save();
            }
        }
        return [
            'products' => $configurableOptions
        ];
    }
}
