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

namespace Magento\Catalog\Test\TestCase\Product;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Catalog\Test\Fixture\Product;

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
        $editProductPage->getMessagesBlock()->assertSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->assertSuccessMessage();
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
