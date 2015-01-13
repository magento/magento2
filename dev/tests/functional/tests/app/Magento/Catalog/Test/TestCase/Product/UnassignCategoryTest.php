<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\Product;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class UnassignCategoryTest
 * Unassign product from category on Product page
 */
class UnassignCategoryTest extends Functional
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
     * Unassigning products from the category on Product Information page
     *
     * @ZephyrId MAGETWO-12417
     * @return void
     */
    public function testUnassignOnProductPage()
    {
        //Data
        $simple = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $simple->switchData('simple');
        $simple->persist();
        //Steps
        $editProductPage = Factory::getPageFactory()->getCatalogProductEdit();
        $editProductPage->open(['id' => $simple->getProductId()]);
        $productForm = $editProductPage->getProductForm();
        $productForm->clearCategorySelect();
        $editProductPage->getFormPageActions()->save();
        //Verifying
        $editProductPage->getMessagesBlock()->waitSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->waitSuccessMessage();
        //Verifying
        $this->assertAbsenceOnCategory($simple);
    }

    /**
     * Assert absence product on category page (frontend)
     *
     * @param Product $product
     * @return void
     */
    protected function assertAbsenceOnCategory(Product $product)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertFalse($productListBlock->isProductVisible($product->getName()));
    }
}
