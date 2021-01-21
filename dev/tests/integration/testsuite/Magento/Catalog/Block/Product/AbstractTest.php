<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Block\Product\Abstract.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
 * @magentoAppArea frontend
 */
class AbstractTest extends TestCase
{
    /**
     * Stub class name for class under test
     */
    const STUB_CLASS = 'Magento_Catalog_Block_Product_AbstractProduct_Stub';

    /**
     * @var AbstractProduct
     */
    protected $block;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Flag is stub class was created
     *
     * @var bool
     */
    protected static $isStubClass = false;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @inheritdoc
     */

    protected function setUp(): void
    {
        if (!self::$isStubClass) {
            $this->getMockForAbstractClass(
                AbstractProduct::class,
                [],
                self::STUB_CLASS,
                false
            );
            self::$isStubClass = true;
        }
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->get(DesignInterface::class)->setDefaultDesignTheme();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(self::STUB_CLASS);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->json = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testGetAddToCartUrlWithProductRequiredOptions(): void
    {
        $product = $this->productRepository->get('simple');
        $url = $this->block->getAddToCartUrl($product);
        $this->assertStringEndsWith('?options=cart', $url);
        $this->assertStringMatchesFormat('%ssimple-product.html%s', $url);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testGetAddToCartUrlWithSimpleProduct(): void
    {
        $product = $this->productRepository->get('simple-1');
        $url = $this->block->getAddToCartUrl($product);
        $this->assertStringEndsWith(sprintf('product/%s/', $product->getId()), $url);
        $this->assertStringContainsString('checkout/cart/add', $url);
    }

    /**
     * @return void
     */
    public function testGetSubmitUrl(): void
    {
        $this->product = $this->productRepository->get('simple');
        /* by default same as add to cart */
        $this->assertStringEndsWith('?options=cart', $this->block->getSubmitUrl($this->product));
        $this->block->setData('submit_route_data', ['route' => 'catalog/product/view']);
        $this->assertStringEndsWith('catalog/product/view/', $this->block->getSubmitUrl($this->product));
    }

    /**
     * @return void
     */
    public function testGetAddToWishlistParams(): void
    {
        $this->product = $this->productRepository->get('simple');
        $json = $this->block->getAddToWishlistParams($this->product);
        $params = (array)$this->json->unserialize($json);
        $data = (array)$params['data'];
        $this->assertEquals($this->product->getId(), $data['product']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/add/',
            $params['action']
        );
    }

    /**
     * @return void
     */
    public function testGetAddToCompareUrl(): void
    {
        $this->assertStringMatchesFormat('%scatalog/product_compare/add/', $this->block->getAddToCompareUrl());
    }

    /**
     * @return void
     */
    public function testGetMinimalQty(): void
    {
        $this->product = $this->productRepository->get('simple');
        $this->assertGreaterThan(0, $this->block->getMinimalQty($this->product));
    }

    /**
     * @return void
     */
    public function testGetReviewsSummaryHtml(): void
    {
        $this->product = $this->productRepository->get('simple');
        $html = $this->block->getReviewsSummaryHtml($this->product, false, true);
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('review', $html);
    }

    /**
     * @return void
     */
    public function testGetProduct(): void
    {
        $this->product = $this->productRepository->get('simple');
        $this->block->setProduct($this->product);
        $this->assertSame($this->product, $this->block->getProduct());
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testGetProductUrl(): void
    {
        $product = $this->productRepository->get('simple');
        $this->assertStringEndsWith('simple-product.html', $this->block->getProductUrl($product));
    }

    /**
     * @return void
     */
    public function testHasProductUrl(): void
    {
        $this->product = $this->productRepository->get('simple');
        $this->assertTrue($this->block->hasProductUrl($this->product));
    }

    /**
     * @return void
     */
    public function testLayoutDependColumnCount(): void
    {
        $this->block->setLayout($this->layout);
        $this->assertEquals(3, $this->block->getColumnCount());
        /* default column count */

        $this->block->addColumnCountLayoutDepend('test', 10);
        $this->assertEquals(10, $this->block->getColumnCountLayoutDepend('test'));
        $this->block->removeColumnCountLayoutDepend('test');
        $this->assertFalse($this->block->getColumnCountLayoutDepend('test'));
    }

    /**
     * @return void
     */
    public function testGetCanShowProductPrice(): void
    {
        $this->product = $this->productRepository->get('simple');
        $this->assertTrue($this->block->getCanShowProductPrice($this->product));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testGetProductPriceHtml(): void
    {
        $product = $this->productRepository->get('simple-1');
        $this->assertEmpty($this->block->getProductPriceHtml($product, FinalPrice::PRICE_CODE));
        $this->layout->createBlock(
            Render::class,
            'product.price.render.default',
            [
                'data' => [
                    'price_render_handle' => 'catalog_product_prices',
                    'use_link_for_as_low_as' => true,
                ],
            ]
        );
        $finalPriceHtml = $this->block->getProductPriceHtml($product, FinalPrice::PRICE_CODE);
        $this->assertStringContainsString('price-' . FinalPrice::PRICE_CODE, $finalPriceHtml);
        $this->assertStringContainsString('product-price-' . $product->getId(), $finalPriceHtml);
    }
}
