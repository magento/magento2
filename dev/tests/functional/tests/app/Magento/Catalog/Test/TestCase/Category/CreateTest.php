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
use Magento\Catalog\Test\Fixture\Category;

/**
 * Class CreateTest
 * Create category test
 */
class CreateTest extends Functional
{
    /**
     * Creating Category from Category page with required fields only
     *
     * @ZephyrId MAGETWO-12513
     */
    public function testWithRequiredFields()
    {
        //Data
        /** @var Category $category */
        $category = Factory::getFixtureFactory()->getMagentoCatalogCategory();
        //Pages & Blocks
        $catalogCategoryPage = Factory::getPageFactory()->getCatalogCategory();
        $treeBlock = $catalogCategoryPage->getTreeBlock();
        $catalogCategoryEditPage = Factory::getPageFactory()->getCatalogCategoryEditId();
        $treeBlockEdit = $catalogCategoryEditPage->getTreeBlock();
        $formBlock = $catalogCategoryEditPage->getFormBlock();
        $actionsBlock = $catalogCategoryEditPage->getPageActionsBlock();
        $messagesBlock = $catalogCategoryEditPage->getMessagesBlock();
        //Steps
        Factory::getApp()->magentoBackendLoginUser();
        $catalogCategoryPage->open();
        $treeBlock->selectCategory($category);
        $treeBlockEdit->addSubcategory();
        $formBlock->fill($category);
        $actionsBlock->save();
        //Verifying
        $messagesBlock->assertSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->assertSuccessMessage();
        //Verifying
        $this->assertCategoryOnFrontend($category);
    }

    /**
     * Verify category on the frontend
     *
     * @param Category $category
     */
    protected function assertCategoryOnFrontend(Category $category)
    {
        //Open created category on frontend
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $frontendHomePage->open();
        $navigationMenu = $frontendHomePage->getTopmenu();
        $navigationMenu->selectCategoryByName($category->getCategoryName());
        $this->assertEquals($category->getCategoryName(), $frontendHomePage->getTitleBlock()->getTitle());
    }
}
