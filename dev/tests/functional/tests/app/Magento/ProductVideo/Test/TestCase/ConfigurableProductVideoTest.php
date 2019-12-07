<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\TestCase;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Config\Test\TestStep\SetupConfigurationStep;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Catalog\Test\Constraint\AssertProductSaveMessage;

/**
 * Steps:
 * 1. Go to backend.
 * 2. Create configurable product with several simple product variations.
 * 3. Add a video to a configurable product
 * 4. Add video to simple products of that configurable product.
 * 5. View that configurable product page on the frontend.
 * 6. Select the product configuration to display the simple product for which the video was added.
 * 7. Perform asserts.
 *
 * @group ProductVideo
 * @ZephyrId MAGETWO-69381
 */
class ConfigurableProductVideoTest extends Injectable
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
    private $productIndex;

    /**
     * Page to create a product.
     *
     * @var CatalogProductNew
     */
    private $productNew;

    /**
     * Product edit page.
     *
     * @var CatalogProductEdit
     */
    private $productEdit;

    /**
     * Factory for creation SetupConfigurationStep.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Configuration data holder.
     *
     * @var string
     */
    private $configData = null;

    /**
     * @param CatalogProductIndex $productIndex
     * @param CatalogProductNew $productNew
     * @param CatalogProductEdit $productEdit
     * @param TestStepFactory $testStepFactory
     */
    public function __inject(
        CatalogProductIndex $productIndex,
        CatalogProductNew $productNew,
        CatalogProductEdit $productEdit,
        TestStepFactory $testStepFactory
    ) {
        $this->productIndex = $productIndex;
        $this->productNew = $productNew;
        $this->productEdit = $productEdit;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * @param CatalogProductSimple $simpleProductVideo
     * @param ConfigurableProduct $product
     * @param AssertProductSaveMessage $assertCreateProducts
     * @param string $variation
     * @param null $configData
     */
    public function test(
        CatalogProductSimple $simpleProductVideo,
        ConfigurableProduct $product,
        AssertProductSaveMessage $assertCreateProducts,
        $variation,
        $configData = null
    ) {
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
        $assertCreateProducts->processAssert($this->productEdit);

        $sku = $product->getConfigurableAttributesData()['matrix'][$variation]['sku'];
        $this->productIndex->open();
        $this->productIndex->getProductGrid()->searchAndOpen(['sku' => $sku]);
        $this->productEdit->getProductForm()->fill($simpleProductVideo);
        $this->productEdit->getFormPageActions()->save();
    }
}
