<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class to check configurable product view behaviour.
 *
 * @see \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var HelperProduct
     */
    private $helperProduct;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->searchBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->productResource = $this->objectManager->create(ProductResource::class);
        $this->product = $this->productRepository->get('configurable');
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Configurable::class);
        $this->block->setProduct($this->product);
        $this->helperProduct = $this->objectManager->get(HelperProduct::class);
        $this->dataObjectFactory = $this->objectManager->get(DataObjectFactory::class);
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
     * Verify configurable option not assigned to current website won't be visible.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_two_websites.php
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testGetAllowProductsNonDefaultWebsite(): void
    {
        // Set current website to non-default.
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore('fixture_second_store');
        // Un-assign simple product from non-default website.
        $simple = $this->productRepository->get('simple_Option_1');
        $simple->setWebsiteIds([1]);
        $this->productRepository->save($simple);
        // Verify only one configurable option will be visible.
        $products = $this->block->getAllowProducts();
        $this->assertEquals(1, count($products));
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
     * @return void
     */
    public function testGetJsonConfigWithPreconfiguredValues(): void
    {
        /** @var ConfigurableAttribute $attribute */
        $attribute = $this->product->getExtensionAttributes()->getConfigurableProductOptions()[0];
        $expectedAttributeValue = [
            $attribute->getAttributeId() => $attribute->getOptions()[0]['value_index'],
        ];
        /** @var DataObject $request */
        $buyRequest = $this->dataObjectFactory->create();
        $buyRequest->setData([
            'qty' => 1,
            'super_attribute' => $expectedAttributeValue,
        ]);
        $this->helperProduct->prepareProductOptions($this->product, $buyRequest);

        $config = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertArrayHasKey('defaultValues', $config);
        $this->assertEquals($expectedAttributeValue, $config['defaultValues']);
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
            $this->assertStringContainsString($simpleProduct->getImage(), $resultImage['thumb']);
            $this->assertStringContainsString($simpleProduct->getImage(), $resultImage['img']);
            $this->assertStringContainsString($simpleProduct->getImage(), $resultImage['full']);
            $this->assertTrue($resultImage['isMain']);
            $this->assertEquals('image', $resultImage['type']);
            $this->assertEquals($i, $resultImage['position']);
            $this->assertNull($resultImage['videoUrl']);
        }
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
        $config = $this->serializer->unserialize($this->block->getJsonConfig())['attributes'] ?? null;
        $this->assertNotNull($config);
        $this->assertConfig(reset($config), $expectedConfig);
    }

    /**
     * @return array
     */
    public static function expectedDataProvider(): array
    {
        return [
            [
                'label' => 'Test Configurable',
                'expectedConfig' => [
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
    private function assertConfig(array $data, array $expectedData): void
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
