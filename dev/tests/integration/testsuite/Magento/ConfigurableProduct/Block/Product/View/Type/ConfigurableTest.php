<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class to check configurable product view behaviour
 *
 * @see \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
 *
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class ConfigurableTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Configurable */
    private $block;

    /** @var Product */
    private $product;

    /** @var LayoutInterface */
    private $layout;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var SerializerInterface */
    private $json;

    /** @var ProductResource */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->product = $this->productRepository->get('configurable');
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Configurable::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->block->setProduct($this->product);
        $this->productResource = $this->objectManager->create(ProductResource::class);
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
            $this->assertInstanceOf(Product::class, $product);
        }
    }

    /**
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $config = $this->json->unserialize($this->block->getJsonConfig());
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals(1, $config['productId']);
        $this->assertArrayHasKey('attributes', $config);
        $this->assertArrayHasKey('template', $config);
        $this->assertArrayHasKey('prices', $config);
        $this->assertArrayHasKey('basePrice', $config['prices']);
    }

    /**
     * @dataProvider expectedDataProvider
     *
     * @param string $label
     * @param array $expectedConfig
     * @return void
     */
    public function testConfigurableProductView(string $label, array $expectedConfig): void
    {
        $attributes = $this->block->decorateArray($this->block->getAllowAttributes());
        $this->assertCount(1, $attributes);
        $attribute = $attributes->getFirstItem();
        $this->assertEquals($label, $attribute->getLabel());
        $config = $this->json->unserialize($this->block->getJsonConfig())['attributes'] ?? null;
        $this->assertNotNull($config);
        $this->assertConfig(reset($config), $expectedConfig);
    }

    /**
     * @return array
     */
    public function expectedDataProvider(): array
    {
        return [
            [
                'label' => 'Test Configurable',
                'config_data' => [
                    'label' => 'Test Configurable',
                    'options' => [
                        [
                            'label' => 'Option 1',
                            'sku' => 'simple_10',
                        ],
                        [
                            'label' => 'Option 2',
                            'sku' => 'simple_20',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Assert that data was generated
     *
     * @param array $data
     * @param array $expectedData
     * @return void
     */
    private function assertConfig($data, $expectedData): void
    {
        $this->assertEquals($expectedData['label'], $data['label']);
        $skus = array_column($expectedData['options'], 'sku');
        $idBySkuMap = $this->productResource->getProductsIdsBySkus($skus);
        foreach ($expectedData['options'] as &$option) {
            $sku = $option['sku'];
            unset($option['sku']);
            $option['products'] = [$idBySkuMap[$sku]];
            foreach ($data['options'] as $actualOption) {
                if ($option['label'] === $actualOption['label']) {
                    unset($actualOption['id']);
                    $this->assertEquals($option, $actualOption);
                }
            }
        }
    }
}
