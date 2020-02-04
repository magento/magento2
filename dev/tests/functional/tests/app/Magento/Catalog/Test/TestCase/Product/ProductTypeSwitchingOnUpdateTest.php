<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config;
use Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Downloadable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Util\Command\Cli\EnvWhitelist;

/**
 * Test Creation for ProductTypeSwitchingOnUpdating
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create product according to dataset
 *
 * Steps:
 * 1. Open backend
 * 2. Go to Products > Catalog
 * 3. Open created product in preconditions
 * 4. Perform Actions from dataset
 * 5. Fill data from dataset
 * 6. Save
 * 7. Perform all assertions
 *
 * @group Products
 * @ZephyrId MAGETWO-29633
 */
class ProductTypeSwitchingOnUpdateTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

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
     * DomainWhitelist CLI
     *
     * @var EnvWhitelist
     */
    private $envWhitelist;

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     * @param EnvWhitelist $envWhitelist
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        FixtureFactory $fixtureFactory,
        EnvWhitelist $envWhitelist
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->fixtureFactory = $fixtureFactory;
        $this->envWhitelist = $envWhitelist;
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
        $this->envWhitelist->addHost('example.com');
        list($fixtureClass, $dataset) = explode('::', $productOrigin);
        $productOrigin = $this->fixtureFactory->createByCode(trim($fixtureClass), ['dataset' => trim($dataset)]);
        $productOrigin->persist();
        list($fixtureClass, $dataset) = explode('::', $product);
        $product = $this->fixtureFactory->createByCode(trim($fixtureClass), ['dataset' => trim($dataset)]);

        // Steps
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $productOrigin->getSku()]);
        $this->performAction($actionName);
        $this->catalogProductEdit->getProductForm()->fill($product);
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
    protected function deleteVariations()
    {
        $this->catalogProductEdit->getProductForm()->openSection('variations');
        /** @var Config $variationsTab */
        $variationsTab = $this->catalogProductEdit->getProductForm()->getSection('variations');
        $variationsTab->deleteVariations();
    }

    /**
     * Clear downloadable product data.
     *
     * @return void
     */
    protected function clearDownloadableData()
    {
        $this->catalogProductEdit->getProductForm()->openSection('downloadable_information');
        /** @var Downloadable $downloadableInfoTab */
        $downloadableInfoTab = $this->catalogProductEdit->getProductForm()->getSection('downloadable_information');
        $downloadableInfoTab->getDownloadableBlock('Links')->clearDownloadableData();
        $downloadableInfoTab->setIsDownloadable('No');
        $this->envWhitelist->removeHost('example.com');
    }
}
