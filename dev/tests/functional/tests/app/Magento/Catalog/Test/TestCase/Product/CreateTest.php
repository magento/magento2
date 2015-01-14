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
 * Class CreateTest
 * Create simple product for BAT
 */
class CreateTest extends Functional
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
     * Creating simple product and assigning it to category
     *
     * @ZephyrId MAGETWO-12514
     * @return void
     */
    public function testCreateProduct()
    {
        $product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $product->switchData('simple');
        //Data
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productForm = $createProductPage->getProductForm();
        //Steps
        $createProductPage->open([
                'type' => $product->getDataConfig()['create_url_params']['type'],
                'set' => $product->getDataConfig()['create_url_params']['set'],
            ]);
        $productForm->fill($product);
        $createProductPage->getFormPageActions()->save();
        //Verifying
        $createProductPage->getMessagesBlock()->waitSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->waitSuccessMessage();
        //Verifying
        $this->assertOnGrid($product);
        $this->assertOnCategory($product);
    }

    /**
     * Assert existing product on admin product grid
     *
     * @param SimpleProduct $product
     * @return void
     */
    protected function assertOnGrid(SimpleProduct $product)
    {
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Grid $gridBlock */
        $gridBlock = $productGridPage->getProductGrid();
        $this->assertTrue($gridBlock->isRowVisible(['sku' => $product->getSku()]));
    }

    /**
     * Assert product data on category and product pages
     *
     * @param SimpleProduct $product
     * @return void
     */
    protected function assertOnCategory(SimpleProduct $product)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getName()));
        $productListBlock->openProductViewPage($product->getName());
        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $this->assertEquals($product->getName(), $productViewBlock->getProductName());
        $price = $productViewBlock->getPriceBlock()->getPrice();
        $this->assertEquals(number_format($product->getProductPrice(), 2), $price);
    }
}
