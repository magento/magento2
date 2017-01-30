<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\TestCase;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Flow:
 *
 * Precondition:
 * 1. Category is created.
 * 2. Bundle product is created.
 *
 * Steps
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS > Catalog.
 * 3. Select a product in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save".
 * 6. Perform asserts
 *
 * @group Bundle_Product_(MX)
 * @ZephyrId MAGETWO-26195
 */
class UpdateBundleProductEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Page product on backend
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Edit page on backend
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Injection data
     *
     * @param CatalogProductIndex $catalogProductIndexNewPage
     * @param CatalogProductEdit $catalogProductEditPage
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndexNewPage,
        CatalogProductEdit $catalogProductEditPage
    ) {
        $this->catalogProductIndex = $catalogProductIndexNewPage;
        $this->catalogProductEdit = $catalogProductEditPage;
    }

    /**
     * Test update bundle product
     *
     * @param BundleProduct $product
     * @param BundleProduct $originalProduct
     * @return array
     */
    public function test(BundleProduct $product, BundleProduct $originalProduct)
    {
        // Preconditions
        $originalProduct->persist();
        $originalCategory = $originalProduct->hasData('category_ids')
            ? $originalProduct->getDataFieldConfig('category_ids')['source']->getCategories()
            : null;
        $category = $product->hasData('category_ids')
            ? $product->getDataFieldConfig('category_ids')['source']->getCategories()
            : $originalCategory;

        // Steps
        $filter = ['sku' => $originalProduct->getSku()];

        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
        $this->catalogProductEdit->getProductForm()->fill($product);
        $this->catalogProductEdit->getFormPageActions()->save();

        return ['category' => $category];
    }
}
