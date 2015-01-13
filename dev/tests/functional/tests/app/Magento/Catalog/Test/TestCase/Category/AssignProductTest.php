<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Fixture\Product;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class AssignProducts
 */
class AssignProductTest extends Functional
{
    /**
     * Creating a subcategory and assign products to the category
     *
     * @ZephyrId MAGETWO-16351
     */
    public function testAssignProducts()
    {
        //Data
        $simple = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $simple->switchData('simple_required');
        $simple->persist();
        $configurable = Factory::getFixtureFactory()->getMagentoConfigurableProductConfigurableProduct();
        $configurable->switchData('configurable_required');
        $configurable->persist();
        $bundle = Factory::getFixtureFactory()->getMagentoBundleBundleFixed();
        $bundle->switchData('bundle_fixed_required');
        $bundle->persist();
        $category = Factory::getFixtureFactory()->getMagentoCatalogCatalogCategory();
        $category->persist();
        //Pages & Blocks
        $catalogCategoryPage = Factory::getPageFactory()->getCatalogCategory();
        $catalogCategoryEditPage = Factory::getPageFactory()->getCatalogCategoryEditId();
        $treeBlock = $catalogCategoryPage->getTreeBlock();
        $formBlock = $catalogCategoryPage->getFormBlock();
        $messagesBlock = $catalogCategoryPage->getMessagesBlock();
        $actionsBlock = $catalogCategoryEditPage->getPageActionsBlock();
        //Steps
        Factory::getApp()->magentoBackendLoginUser();
        $catalogCategoryPage->open();
        $treeBlock->selectCategory($category);
        $formBlock->openTab('category_products');
        $categoryProductsGrid = $formBlock->getCategoryProductsGrid();
        $products = [$simple, $configurable, $bundle];
        /** @var Product $product */
        foreach ($products as $product) {
            $categoryProductsGrid->searchAndSelect(['sku' => $product->getSku()]);
        }
        $actionsBlock->save();
        $messagesBlock->waitSuccessMessage();
        //Clean Cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->waitSuccessMessage();
        //Verifying
        $this->assertProductsOnCategory($category, $products);
    }

    /**
     * Verifying that category present in Frontend with products
     *
     * @param CatalogCategory $category
     * @param array $products
     */
    protected function assertProductsOnCategory(CatalogCategory $category, array $products)
    {
        //Open created category on frontend
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $frontendHomePage->open();
        $navigationMenu = $frontendHomePage->getTopmenu();
        $navigationMenu->selectCategoryByName($category->getName());
        $this->assertEquals($category->getName(), $frontendHomePage->getTitleBlock()->getTitle());
        $productListBlock = $categoryPage->getListProductBlock();
        /** @var Product $product */
        foreach ($products as $product) {
            $this->assertTrue(
                $productListBlock->isProductVisible($product->getName()),
                'Product is absent on category page.'
            );
        }
    }
}
