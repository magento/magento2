<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductActionAttributeEdit;

/**
 * Precondition:
 * 1. Product is created.
 * 2. Product flat is enabled.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Go to Products -> Catalog.
 * 3. Find Product (from preconditions) in Products grid.
 * 4. Select Product's check-box.
 * 5. Select "Update Attributes" value in "Select Product Actions" drop-down list.
 * 6. Click on the "Submit" button.
 * 7. Open "Attributes" tab.
 * 8. Fill data.
 * 9. Click on the "Save" button.
 * 10. Perform asserts.
 *
 * @group Products_(MX)
 * @ZephyrId MAGETWO-21128
 */
class MassProductUpdateTest extends Injectable
{
    /* tags */
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
     * @var CatalogProductActionAttributeEdit
     */
    protected $attributeMassActionPage;

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
     * @param CatalogProductActionAttributeEdit $attributeMassActionPage
     * @return void
     */
    public function __inject(
        CatalogProductIndex $productGrid,
        CatalogProductActionAttributeEdit $attributeMassActionPage
    ) {
        $this->productGrid = $productGrid;
        $this->attributeMassActionPage = $attributeMassActionPage;
    }

    /**
     * Run mass update product simple entity test.
     *
     * @param CatalogProductSimple $initialProduct
     * @param CatalogProductSimple $product
     * @param string $configData
     * @return array
     */
    public function test(CatalogProductSimple $initialProduct, CatalogProductSimple $product, $configData)
    {
        $this->configData = $configData;

        // Preconditions
        $initialProduct->persist();

        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $configData]
        )->run();

        // Steps
        $this->productGrid->open();
        $this->productGrid->getProductGrid()->updateAttributes([['sku' => $initialProduct->getSku()]]);
        $this->attributeMassActionPage->getAttributesBlockForm()->fill($product);
        $this->attributeMassActionPage->getFormPageActions()->save();
        $data = array_merge($initialProduct->getData(), $product->getData());
        $product = $this->objectManager->create(
            'Magento\Catalog\Test\Fixture\CatalogProductSimple',
            ['data' => $data]
        );

        return [
            'category' => $initialProduct->getDataFieldConfig('category_ids')['source']->getCategories()[0],
            'product' => $product,
        ];
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
