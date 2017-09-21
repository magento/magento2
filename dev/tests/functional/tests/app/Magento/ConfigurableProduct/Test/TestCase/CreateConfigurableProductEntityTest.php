<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Config\Test\TestStep\SetupConfigurationStep;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Test Coverage for CreateConfigurableProductEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Two simple products are created.
 * 2. Configurable attribute with two options is created
 * 3. Configurable attribute added to Default template
 *
 * Steps:
 * 1. Go to Backend
 * 2. Open Product -> Catalog
 * 3. Click on narrow near "Add Product" button
 * 4. Select Configurable Product
 * 5. Fill in data according to data sets
 *  5.1 If field "attributeNew/dataset" is not empty - search created attribute by putting it's name
 *      to variation Search field.
 *  5.2 If "attribute/dataset" is not empty- create new Variation Set
 * 6. Save product
 * 7. Perform all assertions
 *
 * @group Configurable_Product
 * @ZephyrId MAGETWO-26041
 */
class CreateConfigurableProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    /* end tags */

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $productIndex;

    /**
     * Page to create a product.
     *
     * @var CatalogProductNew
     */
    protected $productNew;

    /**
     * Factory for creation SetupConfigurationStep.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Configuration data holder.
     *
     * @var string
     */
    protected $configData = null;

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $productIndex
     * @param CatalogProductNew $productNew
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        CatalogProductIndex $productIndex,
        CatalogProductNew $productNew,
        TestStepFactory $testStepFactory
    ) {
        $this->productIndex = $productIndex;
        $this->productNew = $productNew;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Test create catalog Configurable product run.
     *
     * @param ConfigurableProduct $product
     * @param string|null $configData
     * @return void
     */
    public function test(ConfigurableProduct $product, $configData = null)
    {
        //Preconditions
        $this->configData = $configData;
        $this->testStepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => $this->configData, 'flushCache' => true]
        )->run();

        // Steps
        $this->productIndex->open();
        $this->productIndex->getGridPageActionBlock()->addProduct('configurable');
        $this->productNew->getProductForm()->fill($product);
        $this->productNew->getFormPageActions()->save($product);
    }

    /**
     * Revert Display Out Of Stock Products configuration.
     */
    public function teatDown()
    {
        $this->testStepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => $this->configData, 'flushCache' => true]
        )->cleanUp();
    }
}
