<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Category;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogGraphQl\Model\Category\ProductsCountProvider;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see ProductsCountProvider
 */
class ProductsCountProviderTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    private StockConfigurationInterface $stockConfiguration;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface $connection;

    /**
     * @var Select|MockObject
     */
    private Select $select;

    /**
     * @var array<string, array>
     */
    private array $calls = ['from' => [], 'columns' => [], 'where' => [], 'group' => [], 'join' => []];

    /**
     * @var ProductsCountProvider
     */
    private ProductsCountProvider $provider;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->stockConfiguration = $this->createMock(StockConfigurationInterface::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->select = $this->createMock(Select::class);

        $tableMaintainer = $this->createMock(TableMaintainer::class);
        $tableMaintainer->method('getMainTable')->willReturnCallback(
            fn (int $storeId): string => 'catalog_category_product_index_store' . $storeId
        );
        $visibility = $this->createMock(Visibility::class);
        $visibility->method('getVisibleInSiteIds')->willReturn([2, 3, 4]);
        $stockStatusResource = $this->createMock(StockStatusResource::class);
        $stockStatusResource->method('getMainTable')->willReturn('cataloginventory_stock_status');

        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->connection->method('select')->willReturn($this->select);
        $this->connection->method('quoteInto')->willReturnCallback(
            fn (string $text, $value): string => str_replace('?', (string)$value, $text)
        );

        foreach (array_keys($this->calls) as $method) {
            $this->select->method($method)->willReturnCallback(
                function (...$arguments) use ($method): Select {
                    while ($arguments !== [] && end($arguments) === null) {
                        array_pop($arguments);
                    }
                    $this->calls[$method][] = $arguments;
                    return $this->select;
                }
            );
        }

        $this->provider = new ProductsCountProvider(
            $this->resourceConnection,
            $tableMaintainer,
            $visibility,
            $this->stockConfiguration,
            $stockStatusResource
        );
    }

    public function testNonAnchorCategoriesAreCountedByDirectAssignmentWithStockJoin(): void
    {
        $this->stockConfiguration->method('isShowOutOfStock')->willReturn(false);
        $this->stockConfiguration->method('getDefaultScopeId')->willReturn(0);
        $this->connection->expects($this->once())
            ->method('fetchPairs')
            ->with($this->select)
            ->willReturn(['3' => '5', '4' => '0']);

        $this->assertSame([3 => 5, 4 => 0], $this->provider->getProductsCounts(1, [3, 4], false));

        $this->assertSame(
            [[['cat_index' => 'catalog_category_product_index_store1'], []]],
            $this->calls['from']
        );
        $this->assertSame([['cat_index.category_id']], $this->calls['group']);
        $this->assertSame(
            [
                [
                    ['stock_status_index' => 'cataloginventory_stock_status'],
                    'stock_status_index.product_id = cat_index.product_id'
                    . ' AND stock_status_index.website_id = 0'
                    . ' AND stock_status_index.stock_id = 1',
                    [],
                ],
            ],
            $this->calls['join']
        );
        $this->assertSame(
            [
                ['cat_index.store_id = ?', 1],
                ['cat_index.category_id IN (?)', [3, 4]],
                ['cat_index.visibility IN (?)', [2, 3, 4]],
                ['cat_index.is_parent = ?', 1],
                ['stock_status_index.stock_status = ?', 1],
            ],
            $this->calls['where']
        );
    }

    public function testAnchorCategoriesWithOutOfStockShownSkipIsParentAndStockJoin(): void
    {
        $this->stockConfiguration->method('isShowOutOfStock')->willReturn(true);
        $this->connection->expects($this->once())->method('fetchPairs')->willReturn([]);

        $this->assertSame([], $this->provider->getProductsCounts(2, [7], true));

        $this->assertSame([], $this->calls['join']);
        $this->assertSame(
            [
                ['cat_index.store_id = ?', 2],
                ['cat_index.category_id IN (?)', [7]],
                ['cat_index.visibility IN (?)', [2, 3, 4]],
            ],
            $this->calls['where']
        );
    }

    public function testEmptyCategoryListIsNotQueried(): void
    {
        $this->connection->expects($this->never())->method('fetchPairs');

        $this->assertSame([], $this->provider->getProductsCounts(1, [], true));
    }
}
