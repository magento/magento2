<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\ResourceModel\AdminSessionInfo;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Security\Model\AdminSessionInfo;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection testing
 */
class CollectionTest extends TestCase
{
    /** @var Collection */
    protected $collectionMock;

    /** @var DateTime */
    protected $dateTimeMock;

    /** @var AbstractDb */
    protected $resourceMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->dateTimeMock = $this->createMock(DateTime::class);

        $entityFactory = $this->getMockForAbstractClass(EntityFactoryInterface::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $fetchStrategy = $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($select);

        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getConnection', 'getMainTable', 'getTable', 'deleteSessionsOlderThen', 'updateStatusByUserId']
            )
            ->getMockForAbstractClass();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $this->resourceMock->expects($this->any())->method('getMainTable')->willReturn('table_test');
        $this->resourceMock->expects($this->any())->method('getTable')->willReturn('test');

        $this->collectionMock = $this->getMockBuilder(
            Collection::class
        )
            ->setMethods(['addFieldToFilter', 'getResource', 'getConnection'])
            ->setConstructorArgs(
                [
                    $entityFactory,
                    $logger,
                    $fetchStrategy,
                    $eventManager,
                    'dateTime' => $this->dateTimeMock,
                    $connection,
                    $this->resourceMock
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $reflection = new \ReflectionClass(get_class($this->collectionMock));
        $reflectionProperty = $reflection->getProperty('dateTime');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->collectionMock, $this->dateTimeMock);

        $this->collectionMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->resourceMock);
    }

    /**
     * @return void
     */
    public function testFilterByUser()
    {
        $userId = 10;
        $status = 2;
        $sessionIdToExclude = [20, 21, 22];

        $this->collectionMock->expects($this->exactly(3))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['user_id', $userId],
                ['status', $status],
                ['session_id', ['neq' => $sessionIdToExclude]]
            )
            ->willReturnSelf();

        $this->assertEquals(
            $this->collectionMock,
            $this->collectionMock->filterByUser($userId, $status, $sessionIdToExclude)
        );
    }

    /**
     * @return void
     */
    public function testFilterExpiredSessions()
    {
        $sessionLifeTime = '600';
        $timestamp = time();

        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->assertEquals($this->collectionMock, $this->collectionMock->filterExpiredSessions($sessionLifeTime));
    }

    /**
     * @return void
     */
    public function testDeleteSessionsOlderThen()
    {
        $timestamp = time();

        $this->resourceMock->expects($this->any())
            ->method('deleteSessionsOlderThen')
            ->with($timestamp);

        $result = $this->collectionMock->deleteSessionsOlderThen($timestamp);
        $this->assertEquals($this->collectionMock, $result);
    }

    /**
     * @return void
     */
    public function testUpdateActiveSessionsStatus()
    {
        $status = 2;
        $userId = 10;
        $sessionIdToExclude = '20';
        $updateOlderThen = 12345;
        $result = 1;

        $this->resourceMock->expects($this->any())
            ->method('updateStatusByUserId')
            ->with(
                $status,
                $userId,
                [AdminSessionInfo::LOGGED_IN],
                [$sessionIdToExclude],
                $updateOlderThen
            )->willReturn($result);

        $this->assertEquals(
            $result,
            $this->collectionMock->updateActiveSessionsStatus($status, $userId, $sessionIdToExclude, $updateOlderThen)
        );
    }
}
