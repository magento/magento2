<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Resource;

use Magento\TestFramework\Helper\ObjectManager;

class UrlRewriteCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Zend_Db_Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\UrlRewrite\Model\Resource\UrlRewriteCollection
     */
    protected $collection;

    protected function setUp()
    {
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->select = $this->getMock('Zend_Db_Select', ['from', 'where'], [], '', false);
        $this->adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'prepareSqlCondition', 'quoteIdentifier'],
            [],
            '',
            false
        );
        $this->resource = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [],
            '',
            false,
            true,
            true,
            ['getReadConnection', '__wakeup', 'getMainTable', 'getTable']
        );

        $this->select->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());
        $this->adapter->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));
        $this->adapter->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));
        $this->resource->expects($this->any())
            ->method('getReadConnection')
            ->will($this->returnValue($this->adapter));
        $this->resource->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue('test_main_table'));
        $this->resource->expects($this->any())
            ->method('getTable')
            ->with('test_main_table')
            ->will($this->returnValue('test_main_table'));

        $this->collection = (new ObjectManager($this))->getObject(
            'Magento\UrlRewrite\Model\Resource\UrlRewriteCollection',
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
     * @covers \Magento\UrlRewrite\Model\Resource\UrlRewriteCollection
     */
    public function testAddStoreFilterIfStoreIsArray($storeId, $withAdmin, $condition)
    {
        $this->adapter->expects($this->once())
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
     * @covers \Magento\UrlRewrite\Model\Resource\UrlRewriteCollection
     */
    public function testAddStoreFilterIfStoreIsInt($storeId, $withAdmin, $condition)
    {
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $store->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->adapter->expects($this->once())
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
