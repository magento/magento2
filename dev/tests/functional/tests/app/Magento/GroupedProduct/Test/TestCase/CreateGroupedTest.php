<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\TestCase;

use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class CreateGroupedTest
 * Grouped product
 *
 */
class CreateGroupedTest extends Functional
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
     * Creating Grouped product and assigning it to category
     *
     * @ZephyrId MAGETWO-13610
     * @return void
     */
    public function testCreateGroupedProduct()
    {
        //Data
        $product = Factory::getFixtureFactory()->getMagentoGroupedProductGroupedProduct();
        $product->switchData('grouped');
        //Page & Blocks
        $manageProductsGrid = Factory::getPageFactory()->getCatalogProductIndex();
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productForm = $createProductPage->getProductForm();
        //Steps
        $manageProductsGrid->open();
        $manageProductsGrid->getGridPageActionBlock()->addProduct('grouped');
        $productForm->fill($product);
        $createProductPage->getFormPageActions()->save();
        //Verifying
        $createProductPage->getMessagesBlock()->waitSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        //Verifying
        $this->assertOnGrid($product);
        $this->assertOnFrontend($product);
    }

    /**
     * Assert existing product on admin product grid
     *
     * @param GroupedProduct $product
     * @return void
     */
    protected function assertOnGrid($product)
    {
        //Search data
        $search = [
            'sku' => $product->getSku(),
            'type' => 'Grouped Product',
        ];
        //Page & Block
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Grid $gridBlock */
        $gridBlock = $productGridPage->getProductGrid();
        //Assertion
        $this->assertTrue($gridBlock->isRowVisible($search), 'Grouped product was not found.');
    }

    /**
     * Assert Grouped product on Frontend
     *
     * @param GroupedProduct $product
     * @return void
     */
    protected function assertOnFrontend(GroupedProduct $product)
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
        $this->assertTrue(
            $productListBlock->isProductVisible($product->getName()),
            'Product is absent on category page.'
        );
        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $productListBlock->openProductViewPage($product->getName());
        $this->assertEquals(
            $product->getName(),
            $productViewBlock->getProductName(),
            'Product name does not correspond to specified.'
        );

        $optionsOnPage = $productViewBlock->getOptions($product);
        $pageAssociatedProductNames = [];
        foreach ($optionsOnPage['grouped_options'] as $optionOnPage) {
            $pageAssociatedProductNames[] = $optionOnPage['name'];
        }
        $this->assertEquals($product->getAssociatedProductNames(), $pageAssociatedProductNames);
    }
}
