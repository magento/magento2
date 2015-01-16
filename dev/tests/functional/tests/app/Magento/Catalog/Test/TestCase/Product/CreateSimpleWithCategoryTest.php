<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\SimpleProduct;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class CreateSimpleWithCategoryTest
 * Test simple product and category creation
 */
class CreateSimpleWithCategoryTest extends Functional
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
     * Test product create
     *
     * @ZephyrId MAGETWO-13345
     * @return void
     */
    public function testCreateProduct()
    {
        //Data
        $product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $product->switchData('simple_with_new_category');

        //Page & Blocks
        $productListPage = Factory::getPageFactory()->getCatalogProductIndex();
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $addProductBlock = $productListPage->getGridPageActionBlock();
        $productForm = $createProductPage->getProductForm();

        //Steps
        $productListPage->open();
        $addProductBlock->addProduct();
        $productForm->fill($product);
        $productForm->addNewCategory($product);
        $createProductPage->getFormPageActions()->save();

        //Verifying
        $this->assertSuccessMessage("You saved the product.");
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->waitSuccessMessage();
        //Verifying
        $this->assertProductOnFrontend($product);
    }

    /**
     * Assert success message
     *
     * @param string $messageText
     */
    protected function assertSuccessMessage($messageText)
    {
        $productEditPage = Factory::getPageFactory()->getCatalogProductEdit();
        $messageBlock = $productEditPage->getMessagesBlock();
        $this->assertContains(
            $messageText,
            $messageBlock->getSuccessMessages(),
            sprintf('Message "%s" is not appear.', $messageText)
        );
    }

    /**
     * Assert simple product on Frontend
     *
     * @param SimpleProduct $product
     * @return void
     */
    protected function assertProductOnFrontend(SimpleProduct $product)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();

        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getNewCategoryName());

        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue(
            $productListBlock->isProductVisible($product->getName()),
            'Product is absent on category page.'
        );

        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $productListBlock->openProductViewPage($product->getName());
        $this->assertEquals($product->getName(), $productViewBlock->getProductName());
        $price = $productViewBlock->getPriceBlock()->getPrice();
        $this->assertEquals(number_format($product->getProductPrice(), 2), $price);
    }
}
