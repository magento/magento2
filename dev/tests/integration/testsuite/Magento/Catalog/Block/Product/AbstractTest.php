<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Test class for \Magento\Catalog\Block\Product\Abstract.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
 * @magentoAppArea frontend
 */
class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Stub class name for class under test
     */
    const STUB_CLASS = 'Magento_Catalog_Block_Product_AbstractProduct_Stub';

    /**
     * @var \Magento\Catalog\Block\Product\AbstractProduct
     */
    protected $_block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Flag is stub class was created
     *
     * @var bool
     */
    protected static $_isStubClass = false;

    protected function setUp()
    {
        if (!self::$_isStubClass) {
            $this->getMockForAbstractClass(
                'Magento\Catalog\Block\Product\AbstractProduct',
                [],
                self::STUB_CLASS,
                false
            );
            self::$_isStubClass = true;
        }

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDefaultDesignTheme();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            self::STUB_CLASS
        );
        $this->_product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_product->load(1);
        $this->_product->addData(
            [
                'image' => '/m/a/magento_image.jpg',
                'small_image' => '/m/a/magento_image.jpg',
                'thumbnail' => '/m/a/magento_image.jpg',
            ]
        );
        $this->_block->setProduct($this->_product);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddToCartUrl()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $product->load(1);
        $url = $this->_block->getAddToCartUrl($product);
        $this->assertStringEndsWith('?options=cart', $url);
        $this->assertStringMatchesFormat('%ssimple-product.html%s', $url);
    }

    public function testGetSubmitUrl()
    {
        /* by default same as add to cart */
        $this->assertStringEndsWith('?options=cart', $this->_block->getSubmitUrl($this->_product));
        $this->_block->setData('submit_route_data', ['route' => 'catalog/product/view']);
        $this->assertStringEndsWith('catalog/product/view/', $this->_block->getSubmitUrl($this->_product));
    }

    public function testGetAddToWishlistParams()
    {
        $json = $this->_block->getAddToWishlistParams($this->_product);
        $params = (array)json_decode($json);
        $data = (array)$params['data'];
        $this->assertEquals('1', $data['product']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/add/',
            $params['action']
        );
    }

    public function testGetAddToCompareUrl()
    {
        $this->assertStringMatchesFormat('%scatalog/product_compare/add/', $this->_block->getAddToCompareUrl());
    }

    public function testGetMinimalQty()
    {
        $this->assertGreaterThan(0, $this->_block->getMinimalQty($this->_product));
    }

    public function testGetReviewsSummaryHtml()
    {
        $this->_block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface')
        );
        $html = $this->_block->getReviewsSummaryHtml($this->_product, false, true);
        $this->assertNotEmpty($html);
        $this->assertContains('review', $html);
    }

    public function testGetProduct()
    {
        $this->assertSame($this->_product, $this->_block->getProduct());
    }

    public function testGetImageLabel()
    {
        $this->assertEquals('Image Alt Text', $this->_block->getImageLabel());
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductUrl()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        $product->load(1);

        $this->assertStringEndsWith('simple-product.html', $this->_block->getProductUrl($product));
    }

    public function testHasProductUrl()
    {
        $this->assertTrue($this->_block->hasProductUrl($this->_product));
    }

    public function testLayoutDependColumnCount()
    {
        $this->_block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface')
        );
        $this->assertEquals(3, $this->_block->getColumnCount());
        /* default column count */

        $this->_block->addColumnCountLayoutDepend('test', 10);
        $this->assertEquals(10, $this->_block->getColumnCountLayoutDepend('test'));
        $this->_block->removeColumnCountLayoutDepend('test');
        $this->assertFalse($this->_block->getColumnCountLayoutDepend('test'));
    }

    public function testGetCanShowProductPrice()
    {
        $this->assertTrue($this->_block->getCanShowProductPrice($this->_product));
    }

    public function testThumbnail()
    {
        $size = $this->_block->getThumbnailSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/' . $size, $this->_block->getThumbnailUrl($this->_product));
    }

    public function testThumbnailSidebar()
    {
        $size = $this->_block->getThumbnailSidebarSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/' . $size, $this->_block->getThumbnailSidebarUrl($this->_product));
    }

    public function testSmallImage()
    {
        $size = $this->_block->getSmallImageSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/' . $size, $this->_block->getSmallImageUrl($this->_product));
    }

    public function testSmallImageSidebar()
    {
        $size = $this->_block->getSmallImageSidebarSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/' . $size, $this->_block->getSmallImageSidebarUrl($this->_product));
    }

    public function testBaseImage()
    {
        $size = $this->_block->getBaseImageSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/' . $size, $this->_block->getBaseImageUrl($this->_product));
    }

    public function testBaseImageIcon()
    {
        $size = $this->_block->getBaseImageIconSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/' . $size, $this->_block->getBaseImageIconUrl($this->_product));
    }
}
