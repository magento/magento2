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

namespace Magento\Downloadable\Test\TestCase\Create;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;

/**
 * Class LinksPurchasedSeparatelyTest
 */
class LinksPurchasedSeparatelyTest extends Functional
{
    /**
     * Product fixture
     *
     * @var DownloadableProduct
     */
    protected $product;

    protected function setUp()
    {
        $this->product = Factory::getFixtureFactory()
            ->getMagentoDownloadableDownloadableProductLinksPurchasedSeparately();
        $this->product->switchData('downloadable');

        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * Creating Downloadable product with required fields only and assign it to the category
     *
     * @ZephyrId MAGETWO-13595
     * @return void
     */
    public function test()
    {
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $createProductPage->init($this->product);
        $productForm = $createProductPage->getProductForm();

        $createProductPage->open();
        $productForm->fill($this->product);
        $createProductPage->getFormAction()->save();

        $createProductPage->getMessagesBlock()->assertSuccessMessage();

        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->assertSuccessMessage();

        $this->assertOnBackend();
        $this->assertOnFrontend();
    }

    /**
     * Assert existing product on admin product grid
     *
     * @return void
     */
    protected function assertOnBackend()
    {
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        $gridBlock = $productGridPage->getProductGrid();
        $this->assertTrue($gridBlock->isRowVisible(array('sku' => $this->product->getProductSku())));
    }

    /**
     * Assert product data on category and product pages
     *
     * @return void
     */
    protected function assertOnFrontend()
    {
        $product = $this->product;
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();

        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());

        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getProductName()));
        $productListBlock->openProductViewPage($product->getProductName());

        $productViewBlock = $productPage->getViewBlock();
        $this->assertEquals($product->getProductName(), $productViewBlock->getProductName());
        $price = $productViewBlock->getProductPrice();
        $this->assertEquals(number_format($product->getProductPrice(), 2), $price['price_regular_price']);

        $productPage->getDownloadableLinksBlock()
            ->check([['title' => $product->getData('fields/downloadable/link/0/title/value')]]);

        $price = $productViewBlock->getProductPrice();
        $this->assertEquals(
            number_format($product->getProductPrice() + $product->getData('fields/downloadable/link/0/price/value'), 2),
            $price['price_regular_price']
        );
    }
}
