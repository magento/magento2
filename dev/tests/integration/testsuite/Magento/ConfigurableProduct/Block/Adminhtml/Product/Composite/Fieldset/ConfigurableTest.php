<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Configurable block in composite product configuration layout
 *
 * @see \Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset\Configurable
 * @magentoAppArea adminhtml
 */
class ConfigurableTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var SerializerInterface */
    private $serializer;

    /** @var Configurable */
    private $block;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Configurable::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testGetProduct(): void
    {
        $product = $this->productRepository->get('simple-1');
        $this->registerProduct($product);
        $blockProduct = $this->block->getProduct();
        $this->assertSame($product, $blockProduct);
        $this->assertEquals(
            $product->getId(),
            $blockProduct->getId(),
            'The expected product is missing in the Configurable block!'
        );
        $this->assertNotNull($blockProduct->getTypeInstance()->getStoreFilter($blockProduct));
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->registerProduct($product);
        $config = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertTrue($config['disablePriceReload']);
        $this->assertTrue($config['stablePrices']);
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
