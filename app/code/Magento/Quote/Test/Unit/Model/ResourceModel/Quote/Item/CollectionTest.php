<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote\Item;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var EntityFactory|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshotMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            FetchStrategyInterface::class
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->selectMock = $this->createMock(Select::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(AbstractDb::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn(
            $this->connectionMock
        );

        $objectManager = new ObjectManager($this);
        $this->collection = $objectManager->getObject(
            Collection::class,
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
