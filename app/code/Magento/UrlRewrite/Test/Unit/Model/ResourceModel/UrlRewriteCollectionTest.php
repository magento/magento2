<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UrlRewriteCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connection;

    /**
     * @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
     */
    protected $collection;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->select = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['from', 'where']);
        $this->connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'prepareSqlCondition', 'quoteIdentifier']
        );
        $this->resource = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', '__wakeup', 'getMainTable', 'getTable']
        );

        $this->select->expects($this->any())
            ->method('where')
            ->willReturnSelf();
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
            \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection::class,
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
        $store = $this->createMock(\Magento\Store\Model\Store::class);
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
