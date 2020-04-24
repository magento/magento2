<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Test class for \Magento\Catalog\Block\Product\Abstract.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
 * @magentoAppArea frontend
 */
class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Stub class name for class under test
     */
    const STUB_CLASS = 'Magento_Catalog_Block_Product_AbstractProduct_Stub';

    /**
     * @var \Magento\Catalog\Block\Product\AbstractProduct
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Flag is stub class was created
     *
     * @var bool
     */
    protected static $isStubClass = false;

    protected function setUp(): void
    {
        if (!self::$isStubClass) {
            $this->getMockForAbstractClass(
                \Magento\Catalog\Block\Product\AbstractProduct::class,
                [],
                self::STUB_CLASS,
                false
            );
            self::$isStubClass = true;
        }

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $objectManager->get(\Magento\Framework\View\DesignInterface::class)->setDefaultDesignTheme();
        $this->block = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(self::STUB_CLASS);
        $this->productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $this->product = $this->productRepository->get('simple');
        $this->product->addData(
            [
                'image' => '/m/a/magento_image.jpg',
                'small_image' => '/m/a/magento_image.jpg',
                'thumbnail' => '/m/a/magento_image.jpg',
            ]
        );
        $this->block->setProduct($this->product);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddToCartUrl()
    {
        $product = $this->productRepository->get('simple');
        $url = $this->block->getAddToCartUrl($product);
        $this->assertStringEndsWith('?options=cart', $url);
        $this->assertStringMatchesFormat('%ssimple-product.html%s', $url);
    }

    public function testGetSubmitUrl()
    {
        /* by default same as add to cart */
        $this->assertStringEndsWith('?options=cart', $this->block->getSubmitUrl($this->product));
        $this->block->setData('submit_route_data', ['route' => 'catalog/product/view']);
        $this->assertStringEndsWith('catalog/product/view/', $this->block->getSubmitUrl($this->product));
    }

    public function testGetAddToWishlistParams()
    {
        $json = $this->block->getAddToWishlistParams($this->product);
        $params = (array)json_decode($json);
        $data = (array)$params['data'];
        $this->assertEquals($this->product->getId(), $data['product']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/add/',
            $params['action']
        );
    }

    public function testGetAddToCompareUrl()
    {
        $this->assertStringMatchesFormat('%scatalog/product_compare/add/', $this->block->getAddToCompareUrl());
    }

    public function testGetMinimalQty()
    {
        $this->assertGreaterThan(0, $this->block->getMinimalQty($this->product));
    }

    public function testGetReviewsSummaryHtml()
    {
        $this->block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get(\Magento\Framework\View\LayoutInterface::class)
        );
        $html = $this->block->getReviewsSummaryHtml($this->product, false, true);
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('review', $html);
    }

    public function testGetProduct()
    {
        $this->assertSame($this->product, $this->block->getProduct());
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductUrl()
    {
        $product = $this->productRepository->get('simple');
        $this->assertStringEndsWith('simple-product.html', $this->block->getProductUrl($product));
    }

    public function testHasProductUrl()
    {
        $this->assertTrue($this->block->hasProductUrl($this->product));
    }

    public function testLayoutDependColumnCount()
    {
        $this->block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get(\Magento\Framework\View\LayoutInterface::class)
        );
        $this->assertEquals(3, $this->block->getColumnCount());
        /* default column count */

        $this->block->addColumnCountLayoutDepend('test', 10);
        $this->assertEquals(10, $this->block->getColumnCountLayoutDepend('test'));
        $this->block->removeColumnCountLayoutDepend('test');
        $this->assertFalse($this->block->getColumnCountLayoutDepend('test'));
    }

    public function testGetCanShowProductPrice()
    {
        $this->assertTrue($this->block->getCanShowProductPrice($this->product));
    }
}
