<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth\Token;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    protected function setUp()
    {
        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));

        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getConnection'])
            ->getMockForAbstractClass();
        $resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection::class,
            ['resource' => $resource]
        );

        $this->collection = $this->getMockBuilder(
            \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection::class
        )->setConstructorArgs($arguments)
            ->setMethods(['addFilter', 'getSelect', 'getTable', '_initSelect'])
            ->getMock();
    }

    public function testJoinConsumerAsApplication()
    {
        $this->select->expects($this->once())->method('joinLeft');
        $this->collection->expects($this->once())->method('getSelect')->willReturn($this->select);
        $this->collection->joinConsumerAsApplication();
    }

    public function testAddFilterByCustomerId()
    {
        $id = 1;
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.customer_id', $id)
            ->willReturn($this->collection);
        $this->collection->addFilterByCustomerId($id);
    }

    public function testAddFilterByConsumerId()
    {
        $id = 1;
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.consumer_id', $id)
            ->willReturn($this->collection);
        $this->collection->addFilterByConsumerId($id);
    }

    public function testAddFilterByType()
    {
        $type = 'type';
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.type', $type)
            ->willReturn($this->collection);
        $this->collection->addFilterByType($type);
    }

    public function testAddFilterById()
    {
        $id = 1;
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.entity_id', ['in' => $id], 'public')
            ->willReturn($this->collection);
        $this->collection->addFilterById($id);
    }

    public function testAddFilterByRevoked()
    {
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.revoked', 1, 'public')
            ->willReturn($this->collection);
        $this->collection->addFilterByRevoked(true);
    }
}
