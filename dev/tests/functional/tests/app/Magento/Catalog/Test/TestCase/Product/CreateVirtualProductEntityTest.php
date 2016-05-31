<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductVirtual;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateVirtualProductEntity
 *
 * Test Flow:
 * 1. Login as admin.
 * 2. Navigate to the Products > Inventory > Catalog.
 * 3. Click on "+" dropdown and select Virtual Product type.
 * 4. Fill in all data according to data set.
 * 5. Save product.
 * 6. Verify created product.
 *
 * @group Virtual_Product_(CS)
 * @ZephyrId MAGETWO-23417
 */
class CreateVirtualProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Category fixture
     *
     * @var Category
     */
    protected $category;

    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to create a product
     *
     * @var CatalogProductNew
     */
    protected $newProductPage;

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
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @return void
     */
    public function __inject(CatalogProductIndex $productGrid, CatalogProductNew $newProductPage)
    {
        $this->productGrid = $productGrid;
        $this->newProductPage = $newProductPage;
    }

    /**
     * Run create product virtual entity test
     *
     * @param CatalogProductVirtual $product
     * @param Category $category
     * @return void
     */
    public function testCreate(CatalogProductVirtual $product, Category $category)
    {
        // Steps
        $this->productGrid->open();
        $this->productGrid->getGridPageActionBlock()->addProduct('virtual');
        $this->newProductPage->getProductForm()->fill($product, null, $category);
        $this->newProductPage->getFormPageActions()->save();
    }
}
