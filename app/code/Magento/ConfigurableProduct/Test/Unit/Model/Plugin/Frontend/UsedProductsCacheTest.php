<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin\Frontend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Plugin\Frontend\UsedProductsCache;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test case for Magento\ConfigurableProduct\Model\Plugin\Frontend\UsedProductsCache
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UsedProductsCacheTest extends TestCase
{
    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var FrontendInterface|MockObject
     */
    private $cache;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var ProductInterfaceFactory|MockObject
     */
    private $productFactory;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var UsedProductsCache
     */
    private $model;

    /**
     * @var array<string, string>
     */
    private array $cacheStorage = [];

    /**
     * @var array<string, int>
     */
    private array $calls = ['load' => 0, 'save' => 0, 'create' => 0];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->metadataPool = $this->createStub(MetadataPool::class);
        $this->cache = $this->createStub(FrontendInterface::class);
        $this->serializer = $this->createStub(SerializerInterface::class);
        $this->productFactory = $this->createStub(ProductInterfaceFactory::class);
        $this->customerSession = $this->createStub(Session::class);
    }

    /**
     * Test that cache is saved with default expiration time
     *
     * @return void
     */
    public function testDefaultLifeTime(): void
    {
        $lifeTime = 31536000;
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->cache = $this->createMock(FrontendInterface::class);
        $this->model = new UsedProductsCache(
            $this->metadataPool,
            $this->cache,
            $this->serializer,
            $this->productFactory,
            $this->customerSession
        );
        $configurable = $this->createMock(Configurable::class);
        $configurableProduct = $this->createMock(Product::class);
        $configurableProduct->method('getIdentities')
            ->willReturn([]);
        $this->metadataPool
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->createMock(EntityMetadataInterface::class));
        $this->cache->expects($this->once())
            ->method('save')
            ->with($this->anything(), $this->anything(), $this->anything(), $lifeTime)
            ->willReturn(true);
        $this->model->aroundGetUsedProducts(
            $configurable,
            function () {
                $product = $this->createMock(Product::class);
                return [$product];
            },
            $configurableProduct
        );
    }

    /**
     * Test that cache is saved with custom expiration time
     *
     * @return void
     */
    public function testCustomLifeTime(): void
    {
        $lifeTime = 60;
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->cache = $this->createMock(FrontendInterface::class);
        $this->model = new UsedProductsCache(
            $this->metadataPool,
            $this->cache,
            $this->serializer,
            $this->productFactory,
            $this->customerSession,
            $lifeTime
        );
        $configurable = $this->createMock(Configurable::class);
        $configurableProduct = $this->createMock(Product::class);
        $configurableProduct->method('getIdentities')
            ->willReturn([]);
        $this->metadataPool
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->createMock(EntityMetadataInterface::class));
        $this->cache->expects($this->once())
            ->method('save')
            ->with($this->anything(), $this->anything(), $this->anything(), $lifeTime)
            ->willReturn(true);
        $this->model->aroundGetUsedProducts(
            $configurable,
            function () {
                $product = $this->createMock(Product::class);
                return [$product];
            },
            $configurableProduct
        );
    }

    public function testRepeatedHitsReadCacheAndBuildProductsOnce(): void
    {
        $this->useInMemoryCache();
        $configurableProduct = $this->createConfigurableProduct(10);
        $this->primeCache($configurableProduct, [['entity_id' => 1, 'sku' => 'a'], ['entity_id' => 2, 'sku' => 'b']]);
        $this->calls = ['load' => 0, 'save' => 0, 'create' => 0];

        $first = $this->getUsedProducts($configurableProduct, $this->failingProceed());
        $second = $this->getUsedProducts($configurableProduct, $this->failingProceed());

        $this->assertSame(1, $this->calls['load']);
        $this->assertSame(2, $this->calls['create']);
        $this->assertSame(['a', 'b'], $this->getSkus($first));
        $this->assertSame(['a', 'b'], $this->getSkus($second));
        $this->assertNotSame($first[0], $second[0]);
        $this->assertNotSame($first[1], $second[1]);
    }

    public function testMissSavesAndMemoizesForNextCall(): void
    {
        $this->useInMemoryCache();
        $configurableProduct = $this->createConfigurableProduct(10);
        $children = [$this->createProduct(['entity_id' => 1, 'sku' => 'a'])];

        $first = $this->getUsedProducts($configurableProduct, fn () => $children);
        $second = $this->getUsedProducts($configurableProduct, $this->failingProceed());

        $this->assertSame($children, $first);
        $this->assertSame(1, $this->calls['save']);
        $this->assertSame(1, $this->calls['load']);
        $this->assertSame(['a'], $this->getSkus($second));
        $this->assertNotSame($children[0], $second[0]);
    }

    public function testReturnedProductsDoNotShareState(): void
    {
        $this->useInMemoryCache();
        $configurableProduct = $this->createConfigurableProduct(10);
        $this->primeCache($configurableProduct, [['entity_id' => 1, 'sku' => 'a', 'media' => ['image' => 'a.jpg']]]);

        $first = $this->getUsedProducts($configurableProduct, $this->failingProceed());
        $first[0]->setData('sku', 'changed');
        $first[0]->setData('media', ['image' => 'changed.jpg']);
        $first[0]->setData('is_salable', false);
        $second = $this->getUsedProducts($configurableProduct, $this->failingProceed());

        $this->assertSame(['entity_id' => 1, 'sku' => 'a', 'media' => ['image' => 'a.jpg']], $second[0]->getData());
    }

    /**
     * @param string $cachedData
     * @return void
     */
    #[DataProvider('emptyCachedDataProvider')]
    public function testEmptyResultsAreNotMemoized(string $cachedData): void
    {
        $this->useInMemoryCache();
        $configurableProduct = $this->createConfigurableProduct(10);
        $this->primeCache($configurableProduct, []);
        $cacheKey = array_key_first($this->cacheStorage);
        $this->cacheStorage[$cacheKey] = $cachedData;
        $proceedCalls = 0;
        $proceed = function () use (&$proceedCalls) {
            $proceedCalls++;
            return [];
        };

        $this->assertSame([], $this->getUsedProducts($configurableProduct, $proceed));
        $this->assertSame([], $this->getUsedProducts($configurableProduct, $proceed));
        $this->assertSame(2, $proceedCalls);
    }

    /**
     * @return array
     */
    public static function emptyCachedDataProvider(): array
    {
        return [
            'empty list' => ['[]'],
            'null' => ['null'],
            'empty string' => [''],
        ];
    }

    public function testMemoIsKeyedByProductAndRequiredAttributes(): void
    {
        $this->useInMemoryCache();
        $productA = $this->createConfigurableProduct(10);
        $productB = $this->createConfigurableProduct(20);
        $this->getUsedProducts($productA, fn () => [$this->createProduct(['sku' => 'a'])]);
        $this->getUsedProducts($productB, fn () => [$this->createProduct(['sku' => 'b'])]);
        $this->getUsedProducts($productA, fn () => [$this->createProduct(['sku' => 'a-attr'])], [2, 1]);

        $this->assertSame(['a'], $this->getSkus($this->getUsedProducts($productA, $this->failingProceed())));
        $this->assertSame(['b'], $this->getSkus($this->getUsedProducts($productB, $this->failingProceed())));
        $this->assertSame(
            ['a-attr'],
            $this->getSkus($this->getUsedProducts($productA, $this->failingProceed(), [1, 2]))
        );
        $this->assertSame(3, $this->calls['save']);
    }

    public function testInvalidatedCacheEntryIsNotServedFromMemo(): void
    {
        $this->useInMemoryCache();
        $configurableProduct = $this->createConfigurableProduct(10);
        $this->primeCache($configurableProduct, [['sku' => 'a']]);
        $this->getUsedProducts($configurableProduct, $this->failingProceed());
        $this->cacheStorage = [];

        $result = $this->getUsedProducts($configurableProduct, fn () => [$this->createProduct(['sku' => 'fresh'])]);

        $this->assertSame(['fresh'], $this->getSkus($result));
        $this->assertSame(
            ['fresh'],
            $this->getSkus($this->getUsedProducts($configurableProduct, $this->failingProceed()))
        );
    }

    public function testResetStateDropsMemo(): void
    {
        $this->useInMemoryCache();
        $configurableProduct = $this->createConfigurableProduct(10);
        $this->primeCache($configurableProduct, [['sku' => 'a']]);
        $this->calls = ['load' => 0, 'save' => 0, 'create' => 0];

        $this->getUsedProducts($configurableProduct, $this->failingProceed());
        $this->model->_resetState();
        $this->getUsedProducts($configurableProduct, $this->failingProceed());

        $this->assertSame(2, $this->calls['load']);
    }

    /**
     * Back the cache and product factory mocks with in-memory storage and call counters
     *
     * @return void
     */
    private function useInMemoryCache(): void
    {
        $this->cache->method('test')
            ->willReturnCallback(fn (string $id) => isset($this->cacheStorage[$id]) ? time() : false);
        $this->cache->method('load')
            ->willReturnCallback(function (string $id) {
                $this->calls['load']++;
                return $this->cacheStorage[$id] ?? false;
            });
        $this->cache->method('save')
            ->willReturnCallback(function (string $data, string $id) {
                $this->calls['save']++;
                $this->cacheStorage[$id] = $data;
                return true;
            });
        $this->productFactory->method('create')
            ->willReturnCallback(function () {
                $this->calls['create']++;
                return $this->createProduct([]);
            });
        $metadata = $this->createStub(EntityMetadataInterface::class);
        $metadata->method('getLinkField')->willReturn('entity_id');
        $this->metadataPool->method('getMetadata')->willReturn($metadata);
        $this->customerSession->method('getCustomerGroupId')->willReturn(0);
        $this->model = new UsedProductsCache(
            $this->metadataPool,
            $this->cache,
            new Json(),
            $this->productFactory,
            $this->customerSession
        );
    }

    /**
     * Store children for the configurable product in the cache through a separate plugin instance
     *
     * @param Product $configurableProduct
     * @param array $children
     * @return void
     */
    private function primeCache(Product $configurableProduct, array $children): void
    {
        $model = $this->model;
        $this->model = new UsedProductsCache(
            $this->metadataPool,
            $this->cache,
            new Json(),
            $this->productFactory,
            $this->customerSession
        );
        $this->getUsedProducts(
            $configurableProduct,
            fn () => array_map(fn (array $data) => $this->createProduct($data), $children)
        );
        $this->model = $model;
    }

    /**
     * @param Product $configurableProduct
     * @param callable $proceed
     * @param array|null $requiredAttributeIds
     * @return array
     */
    private function getUsedProducts(
        Product $configurableProduct,
        callable $proceed,
        ?array $requiredAttributeIds = null
    ): array {
        return $this->model->aroundGetUsedProducts(
            $this->createStub(Configurable::class),
            $proceed,
            $configurableProduct,
            $requiredAttributeIds
        );
    }

    /**
     * @return callable
     */
    private function failingProceed(): callable
    {
        return function () {
            $this->fail('Used products must be served without calling the subject.');
        };
    }

    /**
     * @param int $linkId
     * @return Product
     */
    private function createConfigurableProduct(int $linkId): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getData')->willReturnMap([['entity_id', null, $linkId]]);
        $product->method('getStoreId')->willReturn(1);
        $product->method('getIdentities')->willReturn([]);

        return $product;
    }

    /**
     * @param array $data
     * @return Product
     */
    private function createProduct(array $data): Product
    {
        $product = (new ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData($data);

        return $product;
    }

    /**
     * @param Product[] $products
     * @return array
     */
    private function getSkus(array $products): array
    {
        return array_map(fn (Product $product) => $product->getData('sku'), $products);
    }
}
