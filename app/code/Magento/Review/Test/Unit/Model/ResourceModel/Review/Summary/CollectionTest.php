<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Model\ResourceModel\Review\Summary;

use Magento\Review\Model\ResourceModel\Review\Summary\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategy\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->fetchStrategyMock = $this->createPartialMock(
            \Magento\Framework\Data\Collection\Db\FetchStrategy\Query::class,
            ['fetchAll']
        );
        $this->entityFactoryMock = $this->createPartialMock(
            \Magento\Framework\Data\Collection\EntityFactory::class,
            ['create']
        );
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->setMethods(['getConnection', 'getMainTable', 'getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'query']
        );
        $selectRenderer = $this->getMockBuilder(\Magento\Framework\DB\Select\SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setMethods(['from'])
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collection = $objectManager->getObject(
            \Magento\Review\Model\ResourceModel\Review\Summary\Collection::class,
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

        $objectMock = $this->createPartialMock(\Magento\Framework\Model\AbstractModel::class, ['setData']);
        $objectMock->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Review\Model\Review\Summary::class)
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

        $objectMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['addData']);
        $objectMock->expects($this->once())
            ->method('addData')
            ->with($data);
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Review\Model\Review\Summary::class)
            ->willReturn($objectMock);

        $this->collection->load();
    }
}
