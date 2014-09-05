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
use Magento\Catalog\Test\Fixture\ConfigurableProduct;

/**
 * Class CreateConfigurableTest
 * Configurable product
 */
class CreateConfigurableTest extends Functional
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
     * Creating configurable product and assigning it to category
     *
     * @ZephyrId MAGETWO-12620
     * @return void
     */
    public function testCreateConfigurableProduct()
    {
        //Data
        $product = Factory::getFixtureFactory()->getMagentoCatalogConfigurableProduct();
        $product->switchData('configurable');
        //Page & Blocks
        $manageProductsGrid = Factory::getPageFactory()->getCatalogProductIndex();
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        //Steps
        $manageProductsGrid->open();
        $manageProductsGrid->getGridPageActionBlock()->addProduct('configurable');
        $productForm = $createProductPage->getProductForm();
        $productForm->fill($product);
        $createProductPage->getFormAction()->saveProduct($createProductPage, $product);
        //Verifying
        $createProductPage->getMessagesBlock()->assertSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->assertSuccessMessage();
        //Verifying
        $this->assertOnGrid($product);
        $this->assertOnFrontend($product);
    }

    /**
     * Assert existing product on admin product grid
     *
     * @param ConfigurableProduct $product
     * @return void
     */
    protected function assertOnGrid($product)
    {
        //Search data
        $configurableSearch = [
            'sku' => $product->getProductSku(),
            'type' => 'Configurable Product'
        ];
        $variationSkus = $product->getVariationSkus();
        //Page & Block
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Grid */
        $gridBlock = $productGridPage->getProductGrid();
        //Assertion
        $this->assertTrue($gridBlock->isRowVisible($configurableSearch), 'Configurable product was not found.');
        foreach ($variationSkus as $sku) {
            $this->assertTrue(
                $gridBlock->isRowVisible(['sku' => $sku, 'type' => 'Simple Product']),
                'Variation with sku "' . $sku . '" was not found.'
            );
        }
    }

    /**
     * Assert configurable product on Frontend
     *
     * @param ConfigurableProduct $product
     * @return void
     */
    protected function assertOnFrontend(ConfigurableProduct $product)
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
        $price = $product->getProductPrice();
        $blockPrice = $productViewBlock->getProductPrice();
        $this->assertEquals(
            number_format($price, 2),
            number_format($blockPrice['price_regular_price'], 2),
            'Product price does not correspond to specified.'
        );
        $this->assertTrue($productViewBlock->verifyProductOptions($product), 'Added configurable options are absent');
    }
}
