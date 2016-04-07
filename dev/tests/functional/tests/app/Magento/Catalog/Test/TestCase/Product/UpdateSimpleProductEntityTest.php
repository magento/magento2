<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Category is created.
 * 2. Product is created and assigned to created category.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS -> Catalog.
 * 3. Select a product in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save".
 * 6. Perform asserts.
 *
 * @group Products_(MX)
 * @ZephyrId MAGETWO-23544, MAGETWO-21125
 */
class UpdateSimpleProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

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
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @return void
     */
    public function __inject(
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage
    ) {
        $this->productGrid = $productGrid;
        $this->editProductPage = $editProductPage;
    }

    /**
     * Run update product simple entity test.
     *
     * @param CatalogProductSimple $initialProduct
     * @param CatalogProductSimple $product
     * @param string $configData
     * @return array
     */
    public function test(CatalogProductSimple $initialProduct, CatalogProductSimple $product, $configData = '')
    {
        $this->configData = $configData;
        // Preconditions
        $initialProduct->persist();
        $initialCategory = $initialProduct->hasData('category_ids')
            ? $initialProduct->getDataFieldConfig('category_ids')['source']->getCategories()[0]
            : null;
        $category = $product->hasData('category_ids') && $product->getCategoryIds()[0]
            ? $product->getDataFieldConfig('category_ids')['source']->getCategories()[0]
            : $initialCategory;

        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $configData]
        )->run();

        // Steps
        $filter = ['sku' => $initialProduct->getSku()];

        $this->productGrid->open();
        $this->productGrid->getProductGrid()->searchAndOpen($filter);
        $this->editProductPage->getProductForm()->fill($product);
        $this->editProductPage->getFormPageActions()->save();

        return ['category' => $category];
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
                'Magento\Config\Test\TestStep\SetupConfigurationStep',
                ['configData' => $this->configData, 'rollback' => true]
            )->run();
        }
    }
}
