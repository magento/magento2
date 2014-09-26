<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Test\TestCase\Category;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Fixture\CatalogCategory;

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
            $categoryProductsGrid->searchAndSelect(['sku' => $product->getProductSku()]);
        }
        $actionsBlock->save();
        $messagesBlock->assertSuccessMessage();
        //Clean Cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->assertSuccessMessage();
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
