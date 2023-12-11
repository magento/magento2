<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Event;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Reports\Model\ResourceModel\Event\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var EntityFactoryInterface|MockObject
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
    protected $managerMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $dbMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->getMockBuilder(
            EntityFactoryInterface::class
        )->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->fetchStrategyMock = $this->getMockBuilder(
            FetchStrategyInterface::class
        )->getMock();
        $this->managerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->onlyMethods(['where', 'from'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $this->dbMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection', '_construct', 'getMainTable', 'getTable'])
            ->addMethods(['getCurrentStoreIds'])
            ->getMock();
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->dbMock);

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->managerMock,
            null,
            $this->resourceMock
        );
    }

    /**
     * @param mixed $ignoreData
     * @param string $ignoreSql
     *
     * @return void
     * @dataProvider ignoresDataProvider
     */
    public function testAddStoreFilter($ignoreData, string $ignoreSql): void
    {
        $typeId = 1;
        $subjectId =2;
        $subtype = 3;
        $limit = 0;
        $stores = [1, 2];

        $this->resourceMock
            ->expects($this->once())
            ->method('getCurrentStoreIds')
            ->willReturn($stores);
        $this->selectMock
            ->method('where')
            ->withConsecutive(
                ['event_type_id = ?', $typeId],
                ['subject_id = ?', $subjectId],
                ['subtype = ?', $subtype],
                ['store_id IN(?)', $stores],
                [$ignoreSql, $ignoreData]
            );

        $this->collection->addRecentlyFiler($typeId, $subjectId, $subtype, $ignoreData, $limit);
    }

    /**
     * @return array
     */
    public function ignoresDataProvider(): array
    {
        return [
            [
                'ignoreData' => 1,
                'ignoreSql' => 'object_id <> ?'
            ],
            [
                'ignoreData' => [1],
                'ignoreSql' => 'object_id NOT IN(?)'
            ]
        ];
    }
}
