<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\VirtualProduct;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class CreateTest
 * Test product creation
 */
class CreateVirtualTest extends Functional
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
     * Creating Virtual product with required fields only and assign it to the category
     *
     * @ZephyrId MAGETWO-13593
     * @return void
     */
    public function testCreateProduct()
    {
        $product = Factory::getFixtureFactory()->getMagentoCatalogVirtualProduct();
        $product->switchData('virtual');
        //Data
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $createProductPage->open(['type' => 'virtual', 'set' => 4]);
        $productForm = $createProductPage->getProductForm();
        //Steps
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
     * @param VirtualProduct $product
     * @return void
     */
    protected function assertOnGrid(VirtualProduct $product)
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
     * @param VirtualProduct $product
     * @return void
     */
    protected function assertOnCategory(VirtualProduct $product)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getName()));
        $productListBlock->openProductViewPage($product->getName());
        //Verification on product detail page
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        $productViewBlock = $productPage->getViewBlock();
        $this->assertEquals($product->getName(), $productViewBlock->getProductName());
        $price = $productViewBlock->getPriceBlock()->getPrice();
        $this->assertEquals(number_format($product->getProductPrice(), 2), $price);
    }
}
