<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model\ResourceModel\Review\Summary;

use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\ResourceModel\Review\Summary\Collection;
use Magento\Review\Model\Review\Summary;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Query|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var EntityFactory|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->fetchStrategyMock = $this->createPartialMock(
            Query::class,
            ['fetchAll']
        );
        $this->entityFactoryMock = $this->createPartialMock(
            EntityFactory::class,
            ['create']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->onlyMethods(['getConnection', 'getMainTable', 'getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionMock = $this->createPartialMock(
            Mysql::class,
            ['select', 'query']
        );
        $selectRenderer = $this->getMockBuilder(SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->onlyMethods(['from'])
            ->setConstructorArgs(['adapter' => $this->connectionMock, 'selectRenderer' => $selectRenderer])
            ->getMock();

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getMainTable')
            ->willReturn('main_table_name');

        $this->resourceMock->expects($this->once())
            ->method('getTable')
            ->willReturnArgument(0);

        $objectManager = new ObjectManager($this);
        $this->collection = $objectManager->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testFetchItem()
    {
        $data = [1 => 'test'];
        $statementMock = $this->createPartialMock(\Zend_Db_Statement_Pdo::class, ['fetch']);
        $statementMock->expects($this->once())
            ->method('fetch')
            ->willReturn($data);

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock, $this->anything())
            ->willReturn($statementMock);

        $objectMock = $this->createPartialMock(AbstractModel::class, ['setData']);
        $objectMock->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with(Summary::class)
            ->willReturn($objectMock);
        $item = $this->collection->fetchItem();

        $this->assertEquals($objectMock, $item);
        $this->assertEquals('id', $item->getIdFieldName());
    }

    public function testLoad()
    {
        $data = [10 => 'test'];
        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->selectMock, [])
            ->willReturn([$data]);

        $objectMock = $this->createPartialMock(DataObject::class, ['addData']);
        $objectMock->expects($this->once())
            ->method('addData')
            ->with($data);
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with(Summary::class)
            ->willReturn($objectMock);

        $this->collection->load();
    }
}
