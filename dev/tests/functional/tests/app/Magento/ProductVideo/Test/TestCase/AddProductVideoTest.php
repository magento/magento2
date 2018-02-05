<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * 2. Open simple product page to create a new one.
 * 3. Click "Add Video" in "Images and Videos" section.
 * 4. Fill fields regarding to Test Data.
 * 5. Click "Save" button on "Add Video" panel.
 * 6. Click "Save" button on product page.
 * 7. Perform asserts.
 *
 * @group ProductVideo_(MX)
 * @ZephyrId MAGETWO-43673
 */
class AddProductVideoTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
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
     * @param string $configData
     * @return array
     */
    public function test(
        CatalogProductSimple $product,
        $configData = null
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();

        // Steps
        // * 1. Go to backend.
        $this->productGrid->open();

        // * 2. Open simple product page to create a new one.
        $this->productGrid->getGridPageActionBlock()->addProduct('simple');

        // * 3. Click "Add Video" in "Images and Videos" section.
        // * 4. Fill fields regarding to Test Data.
        // * 5. Click "Save" button on "Add Video" panel.
        $this->newProductPage->getProductForm()->fill($product);

        // * 6. Click "Save" button on product page.
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
                'Magento\Config\Test\TestStep\SetupConfigurationStep',
                ['configData' => $this->configData, 'rollback' => true]
            )->run();
        }
    }
}
