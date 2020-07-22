<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlRewriteCollectionTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resource;

    /**
     * @var Select|MockObject
     */
    protected $select;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var UrlRewriteCollection
     */
    protected $collection;

    protected function setUp(): void
    {
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->select = $this->createPartialMock(Select::class, ['from', 'where']);
        $this->connectionMock = $this->createPartialMock(
            Mysql::class,
            ['select', 'prepareSqlCondition', 'quoteIdentifier']
        );
        $this->resource = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', '__wakeup', 'getMainTable', 'getTable']
        );

        $this->select->expects($this->any())
            ->method('where')->willReturnSelf();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->select);
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resource->expects($this->any())
            ->method('getMainTable')
            ->willReturn('test_main_table');
        $this->resource->expects($this->any())
            ->method('getTable')
            ->with('test_main_table')
            ->willReturn('test_main_table');

        $this->collection = (new ObjectManager($this))->getObject(
            UrlRewriteCollection::class,
            [
                'storeManager' => $this->storeManager,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * @param array $storeId
     * @param bool $withAdmin
     * @param array $condition
     * @dataProvider dataProviderForTestAddStoreIfStoreIsArray
     * @covers \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
     */
    public function testAddStoreFilterIfStoreIsArray($storeId, $withAdmin, $condition)
    {
        $this->connectionMock->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('store_id', ['in' => $condition]);

        $this->collection->addStoreFilter($storeId, $withAdmin);
    }

    /**
     * @return array
     */
    public function dataProviderForTestAddStoreIfStoreIsArray()
    {
        return [
            [[112, 113], false, [112, 113]],
            [[112, 113], true, [112, 113, 0]],
        ];
    }

    /**
     * @param int $storeId
     * @param bool $withAdmin
     * @param array $condition
     * @dataProvider dataProviderForTestAddStoreFilterIfStoreIsInt
     * @covers \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
     */
    public function testAddStoreFilterIfStoreIsInt($storeId, $withAdmin, $condition)
    {
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $this->connectionMock->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('store_id', ['in' => $condition]);

        $this->collection->addStoreFilter($storeId, $withAdmin);
    }

    /**
     * @return array
     */
    public function dataProviderForTestAddStoreFilterIfStoreIsInt()
    {
        return [
            [112, false, [112]],
            [112, true, [112, 0]],
        ];
    }
}
