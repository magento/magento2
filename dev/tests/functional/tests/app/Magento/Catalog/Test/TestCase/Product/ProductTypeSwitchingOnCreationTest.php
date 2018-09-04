<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Downloadable;

/**
 * Test Creation for ProductTypeSwitchingOnCreation
 *
 * Test Flow:
 * 1. Open backend
 * 2. Go to Products > Catalog
 * 3. Start create product from preconditions (according dataset)
 * 4. Fill data from dataset
 * 5. Save
 * 6. Perform all assertions
 *
 * @group Products
 * @ZephyrId MAGETWO-29398
 */
class ProductTypeSwitchingOnCreationTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Page to create a product
     *
     * @var CatalogProductNew
     */
    protected $catalogProductNew;

    /**
     * Fixture Factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductNew $catalogProductNew
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductNew $catalogProductNew,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductNew = $catalogProductNew;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Run product type switching on creation test
     *
     * @param string $createProduct
     * @param string $product
     * @param string $actionName
     * @return array
     */
    public function test(string $createProduct, string $product, string $actionName = null) : array
    {
        // Steps
        list($fixture, $dataset) = explode('::', $product);
        $product = $this->fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getGridPageActionBlock()->addProduct($createProduct);
        if ($actionName) {
            $this->performAction($actionName);
        }
        $this->catalogProductNew->getProductForm()->fill($product);
        $this->catalogProductNew->getFormPageActions()->save($product);

        return ['product' => $product];
    }

    /**
     * Perform action.
     *
     * @param string $actionName
     * @throws \Exception
     * @return void
     */
    protected function performAction(string $actionName)
    {
        if (method_exists(__CLASS__, $actionName)) {
            $this->$actionName();
        }
    }

    /**
     * Clear downloadable product data.
     *
     * @return void
     */
    protected function clearDownloadableData()
    {
        $this->catalogProductNew->getProductForm()->openSection('downloadable_information');
        /** @var Downloadable $downloadableInfoTab */
        $downloadableInfoTab = $this->catalogProductNew->getProductForm()->getSection('downloadable_information');
        $downloadableInfoTab->getDownloadableBlock('Links')->clearDownloadableData();
        $downloadableInfoTab->setIsDownloadable('No');
    }

    /**
     * Set "Is this downloadable Product?" value.
     *
     * @param string $downloadable
     * @return void
     *
     * @throws \Exception
     */
    protected function setIsDownloadable(string $downloadable = 'Yes')
    {
        $this->catalogProductNew->getProductForm()->openSection('downloadable_information');
        /** @var Downloadable $downloadableInfoTab */
        $downloadableInfoTab = $this->catalogProductNew->getProductForm()->getSection('downloadable_information');
        $downloadableInfoTab->setIsDownloadable($downloadable);
    }
}
