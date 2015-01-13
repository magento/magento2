<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell;
use Magento\Catalog\Test\Fixture\Product;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class UpsellTest
 * Product upsell test
 */
class UpsellTest extends Functional
{
    /**
     * Login into backend area before test
     *
     * @return void
     */
    protected function setUp()
    {
        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * Product Up-selling.  Assign upselling to products and see them related on the front-end.
     *
     * @ZephirId MAGETWO-12391
     * @return void
     */
    public function testCreateUpsell()
    {
        // Precondition: create simple product 1
        $simple1 = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $simple1->switchData('simple');
        $simple1->persist();
        $assignToSimple1 = Factory::getFixtureFactory()->getMagentoCatalogUpsellProducts();
        $assignToSimple1->switchData('add_up_sell_products');
        $verify = [$assignToSimple1->getProduct('simple'), $assignToSimple1->getProduct('configurable')];
        //Data
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $editProductPage = Factory::getPageFactory()->getCatalogProductEdit();
        //Steps
        $productGridPage->open();
        $productGridPage->getProductGrid()->searchAndOpen(['sku' => $simple1->getSku()]);
        $editProductPage->getProductForm()->fill($assignToSimple1);
        $editProductPage->getFormPageActions()->save();
        $editProductPage->getMessagesBlock()->waitSuccessMessage();

        $productGridPage->open();
        $productGridPage->getProductGrid()->searchAndOpen(
            ['sku' => $assignToSimple1->getProduct('configurable')->getSku()]
        );
        $assignToSimple1->switchData('add_up_sell_product');
        $productForm = $editProductPage->getProductForm();
        $productForm->fill($assignToSimple1);
        $editProductPage->getFormPageActions()->save();
        $editProductPage->getMessagesBlock()->waitSuccessMessage();

        $this->assertOnTheFrontend($simple1, $verify);
    }

    /**
     * @param Product $product
     * @param Product[] $assigned
     * @return void
     */
    protected function assertOnTheFrontEnd(Product $product, array $assigned)
    {
        /** @var Product $simple2 */
        /** @var Product $configurable */
        list($simple2, $configurable) = $assigned;
        //Open up simple1 product page
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        Factory::getClientBrowser()->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->assertEquals($product->getName(), $productPage->getViewBlock()->getProductName());

        /** @var \Magento\Catalog\Test\Block\Product\ProductList\Upsell $upsellBlock */
        $upsellBlock = $productPage->getUpsellBlock();
        //Verify upsell simple2 and configurable on Simple1 product page
        $this->assertTrue($upsellBlock->isUpsellProductVisible($simple2->getName()));
        $this->assertTrue($upsellBlock->isUpsellProductVisible($configurable->getName()));
        //Open and verify configurable page
        $upsellBlock->openUpsellProduct($configurable->getName());
        $this->assertEquals($configurable->getName(), $productPage->getViewBlock()->getProductName());
        //Verify upsell simple2 on Configurable product page and open it
        $upsellBlock = $productPage->getUpsellBlock();
        $this->assertTrue($upsellBlock->isUpsellProductVisible($simple2->getName()));
        $upsellBlock->openUpsellProduct($simple2->getName());
        $this->assertEquals($simple2->getName(), $productPage->getViewBlock()->getProductName());
        $this->assertFalse($productPage->getUpsellBlock()->isVisible());
    }
}
