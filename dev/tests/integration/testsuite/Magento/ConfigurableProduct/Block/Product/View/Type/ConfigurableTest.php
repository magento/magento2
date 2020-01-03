<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests class configurable options block on product page.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var Configurable
     */
    private $block;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->searchBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->product = $this->productRepository->get('configurable');
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Configurable::class);
        $this->block->setProduct($this->product);
    }

    /**
     * @return void
     */
    public function testGetAllowAttributes(): void
    {
        $attributes = $this->block->getAllowAttributes();
        $this->assertInstanceOf(Collection::class, $attributes);
        $this->assertGreaterThanOrEqual(1, $attributes->getSize());
    }

    /**
     * @return void
     */
    public function testHasOptions(): void
    {
        $this->assertTrue($this->block->hasOptions());
    }

    /**
     * @return void
     */
    public function testGetAllowProducts(): void
    {
        $products = $this->block->getAllowProducts();
        $this->assertGreaterThanOrEqual(2, count($products));
        foreach ($products as $product) {
            $this->assertInstanceOf(ProductInterface::class, $product);
        }
    }

    /**
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $config = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals(1, $config['productId']);
        $this->assertArrayHasKey('attributes', $config);
        $this->assertArrayHasKey('template', $config);
        $this->assertArrayHasKey('prices', $config);
        $this->assertArrayHasKey('basePrice', $config['prices']);
        $this->assertArrayHasKey('images', $config);
        $this->assertCount(0, $config['images']);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_child_products_with_images.php
     * @return void
     */
    public function testGetJsonConfigWithChildProductsImages(): void
    {
        $config = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('images', $config);
        $this->assertCount(2, $config['images']);
        $products = $this->getProducts(
            $this->product->getExtensionAttributes()->getConfigurableProductLinks()
        );
        $i = 0;
        foreach ($products as $simpleProduct) {
            $i++;
            $resultImage = reset($config['images'][$simpleProduct->getId()]);
            $this->assertContains($simpleProduct->getImage(), $resultImage['thumb']);
            $this->assertContains($simpleProduct->getImage(), $resultImage['img']);
            $this->assertContains($simpleProduct->getImage(), $resultImage['full']);
            $this->assertTrue($resultImage['isMain']);
            $this->assertEquals('image', $resultImage['type']);
            $this->assertEquals($i, $resultImage['position']);
            $this->assertNull($resultImage['videoUrl']);
        }
    }

    /**
     * Returns products by ids list.
     *
     * @param array $productIds
     * @return ProductInterface[]
     */
    private function getProducts(array $productIds): array
    {
        $criteria = $this->searchBuilder->addFilter('entity_id', $productIds, 'in')
            ->create();
        return $this->productRepository->getList($criteria)->getItems();
    }
}
