<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\TestCase;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Products>Inventory>Catalog
 * 3. Click on "+" dropdown and select Bundle Product type
 * 4. Fill in all data according to data set
 * 5. Save product
 * 6. Verify created product
 *
 * @group Bundle_Product_(CS)
 * @ZephyrId MAGETWO-24118
 */
class CreateBundleProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
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
     * New page on backend
     *
     * @var CatalogProductNew
     */
    protected $catalogProductNew;

    /**
     * Persist category
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
     * Filling objects of the class
     *
     * @param CatalogProductIndex $catalogProductIndexNewPage
     * @param CatalogProductNew $catalogProductNewPage
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndexNewPage,
        CatalogProductNew $catalogProductNewPage
    ) {
        $this->catalogProductIndex = $catalogProductIndexNewPage;
        $this->catalogProductNew = $catalogProductNewPage;
    }

    /**
     * Test create bundle product
     *
     * @param BundleProduct $product
     * @param Category $category
     * @return void
     */
    public function test(BundleProduct $product, Category $category)
    {
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getGridPageActionBlock()->addProduct('bundle');
        $productBlockForm = $this->catalogProductNew->getProductForm();
        $productBlockForm->fill($product, null, $category);
        $this->catalogProductNew->getFormPageActions()->save();
    }
}
