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
 * 1. Login as admin
 * 2. Navigate to the Products>Inventory>Catalog
 * 3. Click on Bundle product in the grid to edit it
 * 4. Fill in some Bundle Options data according to data set
 * 5. Delete some Bundle Options data according to data set
 * 6. Save product
 * 7. Verify Bundle Options in the updated product
 *
 * @group Bundle_Product
 * @ZephyrId MAGETWO-26195
 */
class UpdateBundleOptionsTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
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
     * @return void
     */
    public function test(BundleProduct $product, BundleProduct $originalProduct)
    {
        // Preconditions
        $originalProduct->persist();

        // Steps
        $filter = ['sku' => $originalProduct->getSku()];

        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);

        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('bundle');
        $container = $form->getSection('bundle');
        $containerFields = $product->getData()['bundle_selections']['bundle_options_delete'];
        $container->deleteFieldsData($containerFields);

        $form->openSection('product-details');
        $container = $form->getSection('product-details');
        $containerFields = $product->getData();
        unset($containerFields['bundle_selections']);
        $container->setFieldsData($containerFields);

        $this->catalogProductEdit->getFormPageActions()->save();
    }
}
