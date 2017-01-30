<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\TestCase;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create Grouped Product.
 *
 * Steps:
 * 1. Login to the backend.
 * 2. Navigate to Products > Catalog.
 * 3. Open grouped product from preconditions.
 * 4. Fill in data according to dataset.
 * 5. Save the Product.
 * 6. Perform all assertions.
 *
 * @group Grouped_Product_(MX)
 * @ZephyrId MAGETWO-26462
 */
class UpdateGroupedProductEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Page product on backend.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Edit page on backend.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Filling objects of the class.
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
     * Test update grouped product.
     *
     * @param GroupedProduct $product
     * @param GroupedProduct $originalProduct
     * @return void
     */
    public function test(GroupedProduct $product, GroupedProduct $originalProduct)
    {
        // Precondition
        $originalProduct->persist();

        // Steps
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $originalProduct->getSku()]);
        $this->catalogProductEdit->getProductForm()->fill($product);
        $this->catalogProductEdit->getFormPageActions()->save();
    }
}
