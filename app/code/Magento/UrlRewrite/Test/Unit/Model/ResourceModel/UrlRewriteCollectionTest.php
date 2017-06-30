<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UrlRewriteCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
     */
    protected $collection;

    protected function setUp()
    {
        $this->storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->select = $this->getMock(\Magento\Framework\DB\Select::class, ['from', 'where'], [], '', false);
        $this->connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'prepareSqlCondition', 'quoteIdentifier'],
            [],
            '',
            false
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
            ->will($this->returnSelf());
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->resource->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue('test_main_table'));
        $this->resource->expects($this->any())
            ->method('getTable')
            ->with('test_main_table')
            ->will($this->returnValue('test_main_table'));

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
        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $store->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));

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
