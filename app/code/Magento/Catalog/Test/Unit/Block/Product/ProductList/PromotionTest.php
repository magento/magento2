<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ProductList\Promotion;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Pricing\Price\SpecialPriceBulkResolverInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Promotion product list block
 *
 * @covers \Magento\Catalog\Block\Product\ProductList\Promotion
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PromotionTest extends TestCase
{
    /**
     * @var Promotion
     */
    private Promotion $block;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var Context|MockObject
     */
    private MockObject $contextMock;

    /**
     * @var LayerResolver|MockObject
     */
    private MockObject $layerResolverMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private MockObject $productCollectionFactoryMock;

    /**
     * @var Layer|MockObject
     */
    private MockObject $catalogLayerMock;

    /**
     * @var Collection|MockObject
     */
    private MockObject $productCollectionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Prepare ObjectManager for dependencies that parent class retrieves internally
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ],
            [
                SpecialPriceBulkResolverInterface::class,
                $this->createMock(SpecialPriceBulkResolverInterface::class)
            ],
            [
                OutputHelper::class,
                $this->createMock(OutputHelper::class)
            ],
            [
                CollectionFactory::class,
                $this->createMock(CollectionFactory::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->contextMock = $this->createMock(Context::class);
        $this->layerResolverMock = $this->createMock(LayerResolver::class);
        $this->productCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->catalogLayerMock = $this->createMock(Layer::class);
        $this->productCollectionMock = $this->createMock(Collection::class);

        $this->layerResolverMock->method('get')
            ->willReturn($this->catalogLayerMock);

        $this->block = $this->objectManager->getObject(
            Promotion::class,
            [
                'context' => $this->contextMock,
                'postDataHelper' => $this->createMock(PostHelper::class),
                'layerResolver' => $this->layerResolverMock,
                'categoryRepository' => $this->createMock(CategoryRepositoryInterface::class),
                'urlHelper' => $this->createMock(UrlHelper::class),
                'productCollectionFactory' => $this->productCollectionFactoryMock
            ]
        );
    }

    /**
     * Test _getProductCollection creates collection with promotion filter
     *
     * @covers \Magento\Catalog\Block\Product\ProductList\Promotion::_getProductCollection
     * @return void
     */
    public function testGetProductCollectionReturnsPromotionFilteredCollection(): void
    {
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productCollectionMock);

        $this->catalogLayerMock->expects($this->once())
            ->method('prepareProductCollection')
            ->with($this->productCollectionMock);

        $this->productCollectionMock->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('promotion', 1)
            ->willReturnSelf();

        $this->productCollectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->willReturnSelf();

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_getProductCollection');

        $result = $method->invoke($this->block);

        $this->assertSame($this->productCollectionMock, $result);
    }

    /**
     * Test _getProductCollection returns cached collection on subsequent calls
     *
     * @covers \Magento\Catalog\Block\Product\ProductList\Promotion::_getProductCollection
     * @return void
     */
    public function testGetProductCollectionReturnsCachedCollectionOnSubsequentCalls(): void
    {
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productCollectionMock);

        $this->catalogLayerMock->expects($this->once())
            ->method('prepareProductCollection')
            ->with($this->productCollectionMock);

        $this->productCollectionMock->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('promotion', 1)
            ->willReturnSelf();

        $this->productCollectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->willReturnSelf();

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_getProductCollection');

        $firstCall = $method->invoke($this->block);
        $secondCall = $method->invoke($this->block);

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that collection preparation is delegated to catalog layer
     *
     * @covers \Magento\Catalog\Block\Product\ProductList\Promotion::_getProductCollection
     * @return void
     */
    public function testGetProductCollectionDelegatesPreparationToCatalogLayer(): void
    {
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productCollectionMock);

        $this->catalogLayerMock->expects($this->once())
            ->method('prepareProductCollection')
            ->with($this->identicalTo($this->productCollectionMock));

        $this->productCollectionMock->method('addAttributeToFilter')
            ->willReturnSelf();
        $this->productCollectionMock->method('addStoreFilter')
            ->willReturnSelf();

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_getProductCollection');

        $method->invoke($this->block);
    }
}
