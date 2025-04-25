<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use Magento\Catalog\Model\Indexer\Category\Product\Plugin\TableResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class TableResolverTest extends TestCase
{
    /**
     * Tests replacing catalog_category_product_index table name
     *
     * @param int $storeId
     * @param string $tableName
     * @param string $expected
     * @dataProvider afterGetTableNameDataProvider
     */
    public function testAfterGetTableName(int $storeId, string $tableName, string $expected): void
    {
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $storeMock = $this->getMockBuilder(Store::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->method('getId')
            ->willReturn($storeId);

        $storeManagerMock->method('getStore')->willReturn($storeMock);

        $tableResolverMock = $this->getMockBuilder(IndexScopeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tableResolverMock->method('resolve')->willReturn('catalog_category_product_index_store1');

        $subjectMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model = new TableResolver($storeManagerMock, $tableResolverMock);

        $this->assertEquals(
            $expected,
            $model->afterGetTableName($subjectMock, $tableName, 'catalog_category_product_index')
        );
    }

    /**
     * Data provider for testAfterGetTableName
     *
     * @return array
     */
    public static function afterGetTableNameDataProvider(): array
    {
        return [
            [
                'storeId' => 1,
                'tableName' => 'catalog_category_product_index',
                'expected' => 'catalog_category_product_index_store1'
            ],
            [
                'storeId' => 0,
                'tableName' => 'catalog_category_product_index',
                'expected' => 'catalog_category_product_index'
            ],
        ];
    }
}
