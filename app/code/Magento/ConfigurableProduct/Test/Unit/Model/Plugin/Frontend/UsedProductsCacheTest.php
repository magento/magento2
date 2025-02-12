<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Magento\ConfigurableProduct\Model\Plugin\Frontend\UsedProductsCache
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->cache = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->productFactory = $this->createMock(ProductInterfaceFactory::class);
        $this->customerSession = $this->createMock(Session::class);
    }

    /**
     * Test that cache is saved with default expiration time
     *
     * @return void
     */
    public function testDefaultLifeTime(): void
    {
        $lifeTime = 31536000;
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
            ->willReturn($this->getMockForAbstractClass(EntityMetadataInterface::class));
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
            ->willReturn($this->getMockForAbstractClass(EntityMetadataInterface::class));
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
}
