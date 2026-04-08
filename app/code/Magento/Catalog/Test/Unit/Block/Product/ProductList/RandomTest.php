<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Random;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Pricing\Price\SpecialPriceBulkResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Random product list block
 *
 * @covers \Magento\Catalog\Block\Product\ProductList\Random
 */
class RandomTest extends TestCase
{
    /**
     * @var Random
     */
    private Random $block;

    /**
     * @var Layer|MockObject
     */
    private MockObject $layerMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private MockObject $productCollectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private MockObject $productCollectionMock;

    /**
     * @var Select|MockObject
     */
    private MockObject $selectMock;

    /**
     * @var Resolver|MockObject
     */
    private MockObject $layerResolverMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->productCollectionMock = $this->createMock(Collection::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->layerMock = $this->createMock(Layer::class);

        $this->productCollectionMock->method('getSelect')
            ->willReturn($this->selectMock);

        $objectManager->prepareObjectManager([
            [SpecialPriceBulkResolverInterface::class, $this->createMock(SpecialPriceBulkResolverInterface::class)],
            [Output::class, $this->createMock(Output::class)],
            [CollectionFactory::class, $this->productCollectionFactoryMock],
        ]);

        $this->layerResolverMock = $this->createMock(Resolver::class);
        $this->layerResolverMock->method('get')
            ->willReturn($this->layerMock);

        $this->block = $objectManager->getObject(
            Random::class,
            [
                'layerResolver' => $this->layerResolverMock,
                'productCollectionFactory' => $this->productCollectionFactoryMock,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($this->block);
    }

    /**
     * Setup expectations for product collection creation
     *
     * @return void
     */
    private function setupProductCollectionExpectations(): void
    {
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productCollectionMock);
    }

    /**
     * Test that getLoadedProductCollection creates and configures product collection
     *
     * @covers \Magento\Catalog\Block\Product\ProductList\Random::_getProductCollection
     * @return void
     */
    public function testGetLoadedProductCollectionCreatesAndConfiguresCollection(): void
    {
        $numProducts = 5;
        $this->block->setData('num_products', $numProducts);

        $this->setupProductCollectionExpectations();

        $this->layerMock->expects($this->once())
            ->method('prepareProductCollection')
            ->with($this->productCollectionMock);

        $this->selectMock->expects($this->once())
            ->method('order')
            ->with('rand()');

        $this->productCollectionMock->expects($this->once())
            ->method('addStoreFilter');

        $this->productCollectionMock->expects($this->once())
            ->method('setPage')
            ->with(1, $numProducts);

        $result = $this->block->getLoadedProductCollection();

        $this->assertSame($this->productCollectionMock, $result);
    }

    /**
     * Test that getLoadedProductCollection caches collection on subsequent calls
     *
     * @covers \Magento\Catalog\Block\Product\ProductList\Random::_getProductCollection
     * @return void
     */
    public function testGetLoadedProductCollectionCachesCollection(): void
    {
        $this->setupProductCollectionExpectations();

        $firstResult = $this->block->getLoadedProductCollection();
        $secondResult = $this->block->getLoadedProductCollection();

        $this->assertSame($firstResult, $secondResult);
    }

    /**
     * Test getLoadedProductCollection with different numProducts values
     *
     * @covers \Magento\Catalog\Block\Product\ProductList\Random::_getProductCollection
     * @param int|null $numProducts
     * @param int $expectedPageSize
     * @return void
     */
    #[DataProvider('numProductsDataProvider')]
    public function testGetLoadedProductCollectionWithNumProducts(
        ?int $numProducts,
        int $expectedPageSize
    ): void {
        if ($numProducts !== null) {
            $this->block->setData('num_products', $numProducts);
        }

        $this->setupProductCollectionExpectations();

        $this->productCollectionMock->expects($this->once())
            ->method('setPage')
            ->with(1, $expectedPageSize);

        $result = $this->block->getLoadedProductCollection();

        $this->assertSame($this->productCollectionMock, $result);
    }

    /**
     * Data provider for numProducts test scenarios
     *
     * @return array
     */
    public static function numProductsDataProvider(): array
    {
        return [
            'with positive numProducts' => [
                'numProducts' => 10,
                'expectedPageSize' => 10
            ],
            'with zero numProducts' => [
                'numProducts' => 0,
                'expectedPageSize' => 0
            ],
            'with null numProducts defaults to zero' => [
                'numProducts' => null,
                'expectedPageSize' => 0
            ]
        ];
    }
}
