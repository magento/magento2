<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth\Token;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection
 */
class CollectionTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Select|MockObject
     */
    protected $select;

    /**
     * @var Collection|MockObject
     */
    protected $collection;

    protected function setUp(): void
    {
        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

        $resource = $this->createPartialMockWithReflection(
            AbstractDb::class,
            ['__wakeup', 'getConnection', '_construct']
        );
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            Collection::class,
            ['resource' => $resource]
        );

        $this->collection = $this->getMockBuilder(
            Collection::class
        )->setConstructorArgs($arguments)
            ->onlyMethods(['addFilter', 'getSelect', 'getTable', '_initSelect'])
            ->getMock();
    }

    public function testJoinConsumerAsApplication(): void
    {
        $this->select->expects($this->once())->method('joinLeft');
        $this->collection->expects($this->once())->method('getSelect')->willReturn($this->select);
        $this->collection->joinConsumerAsApplication();
    }

    public function testAddFilterByCustomerId(): void
    {
        $id = 1;
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.customer_id', $id)
            ->willReturn($this->collection);
        $this->collection->addFilterByCustomerId($id);
    }

    public function testAddFilterByConsumerId(): void
    {
        $id = 1;
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.consumer_id', $id)
            ->willReturn($this->collection);
        $this->collection->addFilterByConsumerId($id);
    }

    public function testAddFilterByType(): void
    {
        $type = 'type';
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.type', $type)
            ->willReturn($this->collection);
        $this->collection->addFilterByType($type);
    }

    public function testAddFilterById(): void
    {
        $id = 1;
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.entity_id', ['in' => $id], 'public')
            ->willReturn($this->collection);
        $this->collection->addFilterById($id);
    }

    public function testAddFilterByRevoked(): void
    {
        $this->collection->expects($this->once())
            ->method('addFilter')
            ->with('main_table.revoked', 1, 'public')
            ->willReturn($this->collection);
        $this->collection->addFilterByRevoked(true);
    }
}
