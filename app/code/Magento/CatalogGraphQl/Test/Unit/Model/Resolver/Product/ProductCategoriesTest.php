<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductCategories;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see ProductCategories
 */
class ProductCategoriesTest extends TestCase
{
    /**
     * @var ProductCategories
     */
    private ProductCategories $productCategories;

    /**
     * @var IndexScopeResolver|MockObject
     */
    private IndexScopeResolver $indexScopeResolverMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface $adapterInterfaceMock;

    /**
     * @var DimensionFactory|MockObject
     */
    private DimensionFactory $dimensionFactoryMock;

    /**
     * @var Select|MockObject
     */
    private Select $selectMock;

    protected function setUp(): void
    {
        $this->indexScopeResolverMock = $this->createMock(IndexScopeResolver::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->dimensionFactoryMock = $this->createMock(DimensionFactory::class);
        $this->adapterInterfaceMock = $this->createMock(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->productCategories = new ProductCategories(
            $this->indexScopeResolverMock,
            $this->resourceConnectionMock,
            $this->dimensionFactoryMock
        );
    }

    public function testGetCategoryIdsByProduct(): void
    {
        $this->selectMock
            ->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $this->selectMock
            ->expects($this->exactly(2))
            ->method('joinInner')
            ->willReturnSelf();
        $this->selectMock
            ->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $this->adapterInterfaceMock
            ->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->adapterInterfaceMock
            ->expects($this->once())
            ->method('fetchCol')
            ->willReturn([]);
        $this->adapterInterfaceMock
            ->expects($this->exactly(2))
            ->method('quoteInto')
            ->willReturn('');

        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapterInterfaceMock);
        $this->resourceConnectionMock->expects($this->exactly(2))->method('getTableName')->willReturn('TableName');

        $this->productCategories->getCategoryIdsByProduct(1, 1);
    }

    public function testGetCategoryIdsByProductsGroupsCategoryIdsByProduct(): void
    {
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))->method('joinInner')->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('cat_index.product_id IN (?)', [10, 20, 30])
            ->willReturnSelf();
        $this->adapterInterfaceMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->adapterInterfaceMock->expects($this->once())->method('quoteInto')->willReturn('');
        $this->adapterInterfaceMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->selectMock)
            ->willReturn([
                ['product_id' => '10', 'category_id' => '3'],
                ['product_id' => '20', 'category_id' => '3'],
                ['product_id' => '10', 'category_id' => '4'],
            ]);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapterInterfaceMock);
        $this->resourceConnectionMock->expects($this->exactly(2))->method('getTableName')->willReturn('TableName');
        $this->dimensionFactoryMock->expects($this->once())->method('create');
        $this->indexScopeResolverMock->expects($this->once())->method('resolve')->willReturn('IndexTableName');

        $this->assertSame(
            [10 => ['3', '4'], 20 => ['3']],
            $this->productCategories->getCategoryIdsByProducts([10, 20, 30], 1)
        );
    }

    public function testGetCategoryIdsByProductsWithoutProductsDoesNotQuery(): void
    {
        $this->resourceConnectionMock->expects($this->never())->method('getConnection');
        $this->dimensionFactoryMock->expects($this->never())->method('create');
        $this->indexScopeResolverMock->expects($this->never())->method('resolve');
        $this->adapterInterfaceMock->expects($this->never())->method('select');
        $this->selectMock->expects($this->never())->method('from');

        $this->assertSame([], $this->productCategories->getCategoryIdsByProducts([], 1));
    }
}
