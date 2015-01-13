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
 * Edit products
 *
 */
class EditSimpleProductTest extends Functional
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
     * Edit simple product
     *
     * @ZephyrId MAGETWO-12428
     * @return void
     */
    public function testEditProduct()
    {
        $product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $product->switchData('simple');
        $product->persist();
        $editProduct = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $editProduct->switchData('simple_edit_required_fields');

        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $gridBlock = $productGridPage->getProductGrid();
        $editProductPage = Factory::getPageFactory()->getCatalogProductEdit();
        $productForm = $editProductPage->getProductForm();
        $cachePage = Factory::getPageFactory()->getAdminCache();

        $productGridPage->open();
        $gridBlock->searchAndOpen(['sku' => $product->getSku(), 'type' => 'Simple Product']);
        $productForm->fill($editProduct);
        $editProductPage->getFormPageActions()->save();
        //Verifying
        $editProductPage->getMessagesBlock()->waitSuccessMessage();
        // Flush cache
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        //Verifying
        $this->assertOnGrid($editProduct);
        $this->assertOnCategoryPage($editProduct, $product->getCategoryName());
        $this->assertOnProductPage($product, $editProduct);
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
        $gridBlock = $productGridPage->getProductGrid();
        $this->assertTrue($gridBlock->isRowVisible(['sku' => $product->getSku()]));
    }

    /**
     * Assert product data on category page
     *
     * @param SimpleProduct $product
     * @param string $categoryName
     * @return void
     */
    protected function assertOnCategoryPage(SimpleProduct $product, $categoryName)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($categoryName);
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getName()));
    }

    /**
     * Assert product data on product page
     *
     * @param SimpleProduct $productOld
     * @param SimpleProduct $productEdited
     * @return void
     */
    protected function assertOnProductPage(SimpleProduct $productOld, SimpleProduct $productEdited)
    {
        Factory::getClientBrowser()->open($_ENV['app_frontend_url'] . $productOld->getUrlKey() . '.html');
        $productPage = Factory::getPageFactory()->getCatalogProductView();

        $productViewBlock = $productPage->getViewBlock();
        $this->assertEquals($productEdited->getName(), $productViewBlock->getProductName());
        $price = $productViewBlock->getPriceBlock()->getPrice();
        $this->assertEquals(number_format($productEdited->getProductPrice(), 2), $price);
    }
}
