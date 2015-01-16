<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Resource\Block\Grid;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    protected function setUp()
    {
        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));

        $resource = $this->getMockBuilder('Magento\Framework\Model\Resource\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getReadConnection'])
            ->getMockForAbstractClass();
        $resource->expects($this->any())
            ->method('getReadConnection')
            ->will($this->returnValue($connection));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\Cms\Model\Resource\Block\Grid\Collection',
            ['resource' => $resource, 'connection' => $connection]
        );

        $this->collection = $this->getMockBuilder('Magento\Cms\Model\Resource\Block\Grid\Collection')
            ->setConstructorArgs($arguments)
            ->setMethods(['addFilter', '_translateCondition', 'getMainTable'])
            ->getMock();
    }

    public function testAddFieldToFilterSore()
    {
        $storeId = 1;
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with(
                $this->equalTo('store'),
                $this->equalTo(['in' => [$storeId]]),
                $this->equalTo('public')
            );
        $this->collection->addFieldToFilter('store_id', $storeId);
    }

    public function testAddFieldToFilter()
    {
        $field = 'title';
        $value = 'test_filter';
        $searchSql = 'sql query';

        $this->collection->expects($this->once())
            ->method('_translateCondition')
            ->with($field, $value)
            ->will($this->returnValue($searchSql));

        $this->select->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo($searchSql),
                $this->equalTo(null),
                $this->equalTo(\Magento\Framework\DB\Select::TYPE_CONDITION)
            );

        $this->collection->addFieldToFilter($field, $value);
    }
}
