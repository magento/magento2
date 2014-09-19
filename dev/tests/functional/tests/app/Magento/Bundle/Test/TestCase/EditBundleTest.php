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

namespace Magento\Bundle\Test\TestCase;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Bundle\Test\Fixture\Bundle;

/**
 * Class EditBundleTest
 * Edit bundle product test
 */
class EditBundleTest extends Functional
{
    /**
     * Login user to backend
     *
     * @return void
     */
    protected function setUp()
    {
        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * Edit bundle
     *
     * @dataProvider createDataProvider
     * @ZephyrId MAGETWO-12842
     * @ZephyrId MAGETWO-12841
     *
     * @param $fixture
     * @return void
     */
    public function testEditBundle($fixture)
    {
        //Data
        /** @var $product \Magento\Bundle\Test\Fixture\Bundle */
        /** @var $editProduct \Magento\Bundle\Test\Fixture\Bundle */
        $product = Factory::getFixtureFactory()->$fixture();
        $product->switchData('bundle');
        $product->persist();
        $editProduct = Factory::getFixtureFactory()->$fixture();
        $editProduct->switchData('bundle_edit_required_fields');

        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $gridBlock = $productGridPage->getProductGrid();
        $editProductPage = Factory::getPageFactory()->getCatalogProductEdit();
        $productForm = $editProductPage->getProductForm();
        $cachePage = Factory::getPageFactory()->getAdminCache();

        $productGridPage->open();
        $gridBlock->searchAndOpen([
            'sku' => $product->getProductSku(),
            'type' => 'Bundle Product'
        ]);
        $productForm->fill($editProduct);
        $editProductPage->getFormPageActions()->save();
        //Verifying
        $editProductPage->getMessagesBlock()->assertSuccessMessage();
        // Flush cache
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        //Verifying
        $this->assertOnGrid($editProduct);
        $this->assertOnCategory($editProduct, $product->getCategoryName());
    }

    /**
     * Create data provider
     *
     * @return array
     */
    public function createDataProvider()
    {
        return [
            ['getMagentoBundleBundleFixed'],
            ['getMagentoBundleBundleDynamic']
        ];
    }

    /**
     * Assert existing product on admin product grid
     *
     * @param Bundle $product
     * @return void
     */
    protected function assertOnGrid($product)
    {
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        $gridBlock = $productGridPage->getProductGrid();
        $this->assertTrue($gridBlock->isRowVisible(['sku' => $product->getProductSku()]));
    }

    /**
     * Check the product on the category page
     *
     * @param Bundle $product
     * @param string $categoryName
     * @return void
     */
    protected function assertOnCategory($product, $categoryName)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($categoryName);
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getName()));
        $productListBlock->openProductViewPage($product->getName());
        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $this->assertSame($product->getName(), $productViewBlock->getProductName());
        $this->assertEquals(
            $product->getProductPrice(),
            [
                'price_from' => $productViewBlock->getPriceBlock()->getPriceFrom(),
                'price_to' => $productViewBlock->getPriceBlock()->getPriceTo()
            ]
        );
    }
}
