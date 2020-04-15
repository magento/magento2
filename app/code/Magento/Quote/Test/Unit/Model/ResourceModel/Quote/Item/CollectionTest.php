<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote\Item;

use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshotMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn(
            $this->connectionMock
        );

        $objectManager = new ObjectManager($this);
        $this->collection = $objectManager->getObject(
            \Magento\Quote\Model\ResourceModel\Quote\Item\Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->eventManagerMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection::class,
            $this->collection
        );
    }
}
