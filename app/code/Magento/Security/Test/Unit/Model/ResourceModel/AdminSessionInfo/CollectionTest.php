<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\ResourceModel\AdminSessionInfo;

/**
 * Test class for \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection testing
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection */
    protected $collectionMock;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime */
    protected $dateTimeMock;

    /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb */
    protected $resourceMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->dateTimeMock = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\DateTime::class,
            [],
            [],
            '',
            false
        );

        $entityFactory = $this->getMock(
            '\Magento\Framework\Data\Collection\EntityFactoryInterface',
            [],
            [],
            '',
            false
        );
        $logger = $this->getMock(
            '\Psr\Log\LoggerInterface',
            [],
            [],
            '',
            false
        );
        $fetchStrategy = $this->getMock(
            '\Magento\Framework\Data\Collection\Db\FetchStrategyInterface',
            [],
            [],
            '',
            false
        );
        $eventManager = $this->getMock(
            '\Magento\Framework\Event\ManagerInterface',
            [],
            [],
            '',
            false
        );

        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($select);

        $this->resourceMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(
                ['getConnection', 'getMainTable', 'getTable', 'deleteSessionsOlderThen', 'updateStatusByUserId']
            )
            ->getMockForAbstractClass();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $this->resourceMock->expects($this->any())->method('getMainTable')->willReturn('table_test');
        $this->resourceMock->expects($this->any())->method('getTable')->willReturn('test');

        $this->collectionMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection',
            ['addFieldToFilter'],
            [$entityFactory, $logger, $fetchStrategy, $eventManager,
                $this->dateTimeMock,
                $connection, $this->resourceMock],
            '',
            true
        );

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
            ->with(
                'updated_at',
                ['gt' => $this->collectionMock->getConnection()->formatDate($timestamp - $sessionLifeTime)]
            )
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

        $this->collectionMock->deleteSessionsOlderThen($timestamp);
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
                [\Magento\Security\Model\AdminSessionInfo::LOGGED_IN],
                [$sessionIdToExclude],
                $updateOlderThen
            )->willReturn($result);

        $this->assertEquals(
            $result,
            $this->collectionMock->updateActiveSessionsStatus($status, $userId, $sessionIdToExclude, $updateOlderThen)
        );
    }
}
