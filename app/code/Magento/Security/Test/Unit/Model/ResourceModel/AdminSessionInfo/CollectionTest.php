<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\ResourceModel\AdminSessionInfo;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Security\Model\AdminSessionInfo;
use Magento\Security\Model\ResourceModel\AdminSessionInfo as AdminSessionInfoResource;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection testing
 */
class CollectionTest extends TestCase
{
    /** @var Collection */
    protected $collectionMock;

    /** @var DateTime */
    protected $dateTimeMock;

    /** @var AdminSessionInfoResource */
    protected $resourceMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->dateTimeMock = $this->createMock(DateTime::class);

        $select = $this->createMock(Select::class);

        $connection = $this->createMock(Mysql::class);
        $connection->expects($this->any())->method('select')->willReturn($select);

        $this->resourceMock = $this->createPartialMock(
            AdminSessionInfoResource::class,
            ['deleteSessionsOlderThen', 'updateStatusByUserId', 'getConnection', 'getMainTable', 'getTable']
        );

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $this->resourceMock->expects($this->any())->method('getMainTable')->willReturn('table_test');
        $this->resourceMock->expects($this->any())->method('getTable')->willReturn('test');

        $this->collectionMock = $this->createPartialMock(
            Collection::class,
            ['addFieldToFilter', 'getResource', 'getConnection']
        );

        $this->collectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $reflection = new \ReflectionClass(get_class($this->collectionMock));
        $reflectionProperty = $reflection->getProperty('dateTime');
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
            ->willReturnCallback(function ($arg1, $arg2) use ($userId, $status, $sessionIdToExclude) {
                if ($arg1 == 'user_id' && $arg2 == $userId) {
                    return $this;
                } elseif ($arg1 == 'status' && $arg2 == $status) {
                    return $this;
                } elseif ($arg1 == 'id' && $arg2 == ['neq' => $sessionIdToExclude]) {
                    return $this;
                }
            });

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
