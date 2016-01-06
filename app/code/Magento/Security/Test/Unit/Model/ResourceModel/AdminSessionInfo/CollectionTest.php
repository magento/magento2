<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\ResourceModel\AdminSessionInfo;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection testing
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection */
    protected $collectionMock;

    /** @var \Magento\Security\Helper\SecurityConfig */
    protected $securityConfigMock;

    /** @var \Magento\Framework\Stdlib\DateTime */
    protected $dateTimeMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->securityConfigMock = $this->getMock(
            '\Magento\Security\Helper\SecurityConfig',
            [],
            [],
            '',
            false
        );

        $this->dateTimeMock = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime',
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

        $resource = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getMainTable', 'getTable'])
            ->getMockForAbstractClass();
        $resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $resource->expects($this->any())->method('getMainTable')->willReturn('table_test');
        $resource->expects($this->any())->method('getTable')->willReturn('test');

        $this->collectionMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection',
            ['addFieldToFilter'],
            [$entityFactory, $logger, $fetchStrategy, $eventManager,
                $this->securityConfigMock, $this->dateTimeMock,
                $connection, $resource],
            '',
            true
        );

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

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with(
                'updated_at',
                [
                    'gt' => $this->dateTimeMock->formatDate(
                        $this->securityConfigMock->getCurrentTimestamp() - $sessionLifeTime
                    )
                ]
            )
            ->willReturnSelf();

        $this->assertEquals($this->collectionMock, $this->collectionMock->filterExpiredSessions($sessionLifeTime));
    }
}
