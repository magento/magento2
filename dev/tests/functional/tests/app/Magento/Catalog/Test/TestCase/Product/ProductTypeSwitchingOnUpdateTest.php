<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config;
use Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for ProductTypeSwitchingOnUpdating
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create product according to dataSet
 *
 * Steps:
 * 1. Open backend
 * 2. Go to Products > Catalog
 * 3. Open created product in preconditions
 * 4. Perform Actions from dataSet
 * 5. Fill data from dataSet
 * 6. Save
 * 7. Perform all assertions
 *
 * @group Products_(MX)
 * @ZephyrId MAGETWO-29633
 */
class ProductTypeSwitchingOnUpdateTest extends Injectable
{
    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Page to update a product.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Fixture Factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Run product type switching on updating test.
     *
     * @param string $productOrigin
     * @param string $product
     * @param string $actionName
     * @return array
     */
    public function test($productOrigin, $product, $actionName)
    {
        // Preconditions
        list($fixtureClass, $dataSet) = explode('::', $productOrigin);
        $productOrigin = $this->fixtureFactory->createByCode(trim($fixtureClass), ['dataSet' => trim($dataSet)]);
        $productOrigin->persist();
        list($fixtureClass, $dataSet) = explode('::', $product);
        $product = $this->fixtureFactory->createByCode(trim($fixtureClass), ['dataSet' => trim($dataSet)]);

        // Steps
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $productOrigin->getSku()]);
        $this->catalogProductEdit->getProductForm()->fill($product);
        $this->performAction($actionName);
        $this->catalogProductEdit->getFormPageActions()->save($product);

        return ['product' => $product];
    }

    /**
     * Perform action.
     *
     * @param string $actionName
     * @throws \Exception
     * @return void
     */
    protected function performAction($actionName)
    {
        if (method_exists(__CLASS__, $actionName)) {
            $this->$actionName();
        }
    }

    /**
     * Delete attributes.
     *
     * @return void
     */
    protected function deleteAttributes()
    {
        $this->catalogProductEdit->getProductForm()->openTab('variations');
        /** @var Config $variationsTab */
        $variationsTab = $this->catalogProductEdit->getProductForm()->getTabElement('variations');
        $variationsTab->deleteAttributes();
    }

    /**
     * Clear downloadable product data.
     *
     * @return void
     */
    protected function clearDownloadableData()
    {
        $this->catalogProductEdit->getProductForm()->openTab('downloadable_information');
        /** @var Downloadable $downloadableInfoTab */
        $downloadableInfoTab = $this->catalogProductEdit->getProductForm()->getTabElement('downloadable_information');
        $downloadableInfoTab->getDownloadableBlock('Links')->clearDownloadableData();
    }
}
