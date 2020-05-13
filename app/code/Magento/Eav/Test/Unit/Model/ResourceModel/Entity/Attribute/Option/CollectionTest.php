<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Entity\Attribute\Option;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CollectionTest extends TestCase
{
    /**
     * @var Collection|MockObject
     */
    protected $model;

    /**
     * @var EntityFactory|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $coreResourceMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->createMock(
            FetchStrategyInterface::class
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->coreResourceMock = $this->createMock(ResourceConnection::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->resourceMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getConnection', 'getMainTable', 'getTable']
        );
        $this->selectMock = $this->createMock(Select::class);

        $this->coreResourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->coreResourceMock->expects($this->any())->method('getTableName')->with('eav_attribute_option_value')
            ->willReturn(null);

        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);
        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->willReturnArgument(0);

        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->willReturn(
            $this->connectionMock
        );
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getMainTable'
        )->willReturn(
            'eav_attribute_option'
        );
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getTable'
        )->with(
            'eav_attribute_option'
        )->willReturn(
            'eav_attribute_option'
        );

        $this->model = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->coreResourceMock,
            $this->storeManagerMock,
            null,
            $this->resourceMock
        );
    }

    public function testSetIdFilter()
    {
        $this->connectionMock->expects(
            $this->once()
        )->method(
            'prepareSqlCondition'
        )->with(
            'main_table.option_id',
            ['in' => 1]
        )->willReturn(
            'main_table.option_id IN (1)'
        );

        $this->selectMock->expects(
            $this->once()
        )->method(
            'where'
        )->with(
            'main_table.option_id IN (1)',
            null,
            'TYPE_CONDITION'
        )->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setIdFilter(1));
    }
}
