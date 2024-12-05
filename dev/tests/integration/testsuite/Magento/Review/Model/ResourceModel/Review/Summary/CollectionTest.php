<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\ResourceModel\Review\Summary;

use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\ResourceModel\Review\Summary\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Tests some functionality of the Review Summary collection
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var Query
     */
    protected Query $fetchStrategyMock;

    /**
     * @var EntityFactory
     */
    protected EntityFactory $entityFactoryMock;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $loggerMock;

    /**
     * @var AbstractDb
     */
    protected AbstractDb $resourceMock;

    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $connectionMock;

    /**
     * @var Select
     */
    protected Select $selectMock;

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
            ->onlyMethods(['where'])
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

    /**
     * @param array|int $storeId
     * @param string $expectedQuery
     * @dataProvider storeIdDataProvider
     */
    public function testAddStoreFilter(array|int $storeId, string $expectedQuery)
    {
        $this->selectMock->expects($this->once())->method('where')->with($expectedQuery, $storeId);
        $this->collection->addStoreFilter($storeId);
    }

    /**
     * @return array
     */
    public static function storeIdDataProvider(): array
    {
        return [
            [1, 'store_id = ?'],
            [[1,2], 'store_id IN (?)']
        ];
    }
}
