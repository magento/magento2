<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Youtube API Key is set.
 *
 * Steps:
 * 1. Go to backend.
 * 2. Open simple product page to create a new product.
 * 3. Click "Add Video" in "Images and Videos" section.
 * 4. Fill fields regarding to Test Data.
 * 5. Click "Save" button on "Add Video" panel.
 * 6. Click on video preview.
 * 7. Fill fields regarding to Test Data.
 * 8. Click "Save" button on "Edit Video" panel.
 * 9. Click "Save" button on product page
 * 10. Perform asserts.
 *
 * @group ProductVideo
 * @ZephyrId MAGETWO-43664, @ZephyrId MAGETWO-43656, @ZephyrId MAGETWO-43661, @ZephyrId MAGETWO-43663
 */
class UpdateProductVideoTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'extended_acceptance_test';
    const MVP = 'yes';
    const STABLE = 'no';
    /* end tags */

    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * @var CatalogProductNew
     */
    protected $newProductPage;

    /**
     * Configuration data
     *
     * @var string
     */
    protected $configData;

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     */
    public function __inject(
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage
    ) {
        $this->productGrid = $productGrid;
        $this->newProductPage = $newProductPage;
    }

    /**
     * Run update product simple entity test.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductSimple $productVideo
     * @param null $configData
     * @return array
     */
    public function test(
        CatalogProductSimple $product,
        CatalogProductSimple $productVideo,
        $configData = null
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Steps
        // * 1. Go to backend.
        $this->productGrid->open();
        // * 2. Open simple product page to create a new product.
        $this->productGrid->getGridPageActionBlock()->addProduct('simple');

        // * 3. Click "Add Video" in "Images and Videos" section.
        // * 4. Fill fields regarding to Test Data.
        // * 5. Click "Save" button on "Add Video" panel.
        $this->newProductPage->getProductForm()->fill($productVideo);

        // * 6. Click on video preview.
        // * 7. Fill fields regarding to Test Data.
        // * 8. Click "Save" button on "Edit Video" panel.

        $this->newProductPage->getProductForm()->fill($product);

        // * 9. Click "Save" button on product page
        $this->newProductPage->getFormPageActions()->save();

        return ['product' => $product];
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
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData, 'rollback' => true]
            )->run();
        }
    }
}
