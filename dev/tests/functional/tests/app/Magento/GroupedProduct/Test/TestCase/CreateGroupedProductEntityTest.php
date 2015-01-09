<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateGroupedProductEntity
 *
 * Preconditions:
 * 1. Simple product is created.
 * 2. Virtual product is created.
 *
 * Test Flow:
 * 1. Login to the backend.
 * 2. Navigate to Products > Catalog.
 * 3. Start to create Grouped Product.
 * 4. Fill in data according to data set.
 * 5. Click "Add Products to Group" button and select products'.
 * 6. Click "Add Selected Product" button
 * 7. Save the Product.
 * 8. Perform assertions.
 *
 * @group Grouped_Product_(MX)
 * @ZephyrId MAGETWO-24877
 */
class CreateGroupedProductEntityTest extends Injectable
{
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
     * @param CatalogCategory $category
     * @return array
     */
    public function __prepare(CatalogCategory $category)
    {
        $category->persist();
        return ['category' => $category];
    }

    /**
     * Injection pages
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
     * Test create grouped product
     *
     * @param GroupedProductInjectable $product
     * @param CatalogCategory $category
     * @return void
     */
    public function test(GroupedProductInjectable $product, CatalogCategory $category)
    {
        //Steps
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getGridPageActionBlock()->addProduct('grouped');
        $this->catalogProductNew->getProductForm()->fill($product, null, $category);
        $this->catalogProductNew->getFormPageActions()->save();
    }
}
