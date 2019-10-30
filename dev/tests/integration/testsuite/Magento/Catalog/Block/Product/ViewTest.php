<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks product view block.
 *
 * @see \Magento\Catalog\Block\Product\View
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class ViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var View */
    private $_block;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /** @var Json */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->_block = $this->objectManager->create(View::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->json = $this->objectManager->get(Json::class);
    }

    /**
     * @return void
     */
    public function testSetLayout(): void
    {
        $productView = $this->layout->createBlock(View::class);

        $this->assertInstanceOf(LayoutInterface::class, $productView->getLayout());
    }

    /**
     * @return void
     */
    public function testGetProduct(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);

        $this->assertNotEmpty($this->_block->getProduct()->getId());
        $this->assertEquals($product->getId(), $this->_block->getProduct()->getId());

        $this->registry->unregister('product');
        $this->_block->setProductId($product->getId());

        $this->assertEquals($product->getId(), $this->_block->getProduct()->getId());
    }

    /**
     * @return void
     */
    public function testCanEmailToFriend(): void
    {
        $this->assertFalse($this->_block->canEmailToFriend());
    }

    /**
     * @return void
     */
    public function testGetAddToCartUrl(): void
    {
        $product = $this->productRepository->get('simple');
        $url = $this->_block->getAddToCartUrl($product);

        $this->assertStringMatchesFormat(
            '%scheckout/cart/add/%sproduct/' . $product->getId() . '/',
            $url
        );
    }

    /**
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);
        $config = $this->json->unserialize($this->_block->getJsonConfig());

        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals($product->getId(), $config['productId']);
    }

    /**
     * @return void
     */
    public function testHasOptions(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);

        $this->assertTrue($this->_block->hasOptions());
    }

    /**
     * @return void
     */
    public function testHasRequiredOptions(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);

        $this->assertTrue($this->_block->hasRequiredOptions());
    }

    /**
     * @return void
     */
    public function testStartBundleCustomization(): void
    {
        $this->markTestSkipped("Functionality not implemented in Magento 1.x. Implemented in Magento 2");

        $this->assertFalse($this->_block->startBundleCustomization());
    }

    /**
     * @magentoAppArea frontend
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     */
    public function testAddToCartBlockInvisibility(): void
    {
        $outOfStockProduct = $this->productRepository->get('simple-out-of-stock');
        $this->registerProduct($outOfStockProduct);
        $this->_block->setTemplate('Magento_Catalog::product/view/addtocart.phtml');
        $output = $this->_block->toHtml();

        $this->assertNotContains((string)__('Add to Cart'), $output);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testAddToCartBlockVisibility(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);
        $this->_block->setTemplate('Magento_Catalog::product/view/addtocart.phtml');
        $output = $this->_block->toHtml();

        $this->assertContains((string)__('Add to Cart'), $output);
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }
}
