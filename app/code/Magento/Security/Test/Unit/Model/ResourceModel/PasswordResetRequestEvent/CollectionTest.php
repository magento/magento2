<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\ResourceModel\PasswordResetRequestEvent;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection;
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

    /** @var Select */
    protected $selectMock;

    /** @var AbstractDb */
    protected $resourceMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $entityFactory = $this->getMockForAbstractClass(EntityFactoryInterface::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $fetchStrategy = $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->dateTimeMock = $this->createPartialMock(
            DateTime::class,
            ['gmtTimestamp']
        );

        $this->selectMock = $this->createPartialMock(Select::class, ['limit', 'from']);

        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getMainTable', 'getTable', 'deleteRecordsOlderThen'])
            ->getMockForAbstractClass();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $this->resourceMock->expects($this->any())->method('getMainTable')->willReturn('table_test');
        $this->resourceMock->expects($this->any())->method('getTable')->willReturn('test');

        $this->collectionMock = $this->getMockBuilder(
            Collection::class
        )
            ->setMethods(['addFieldToFilter', 'addOrder', 'getSelect', 'getResource', 'getConnection'])
            ->setConstructorArgs(
                [
                    $entityFactory,
                    $logger,
                    $fetchStrategy,
                    $eventManager,
                    $this->dateTimeMock,
                    $connection,
                    $this->resourceMock
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $reflection = new \ReflectionClass(get_class($this->collectionMock));
        $reflectionProperty = $reflection->getProperty('dateTime');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->collectionMock, $this->dateTimeMock);

        $this->collectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $this->collectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($this->selectMock);

        $this->collectionMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->resourceMock);
    }

    /**
     * @return void
     */
    public function testFilterByAccountReference()
    {
        $reference = '12345';

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('account_reference', $reference)
            ->willReturnSelf();

        $this->assertEquals(
            $this->collectionMock,
            $this->collectionMock->filterByAccountReference($reference)
        );
    }

    /**
     * @return void
     */
    public function testFilterByIp()
    {
        $ip = 12345;

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('ip', $ip)
            ->willReturnSelf();

        $this->assertEquals(
            $this->collectionMock,
            $this->collectionMock->filterByIp($ip)
        );
    }

    /**
     * @return void
     */
    public function testFilterByRequestType()
    {
        $requestType = 3;

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('request_type', $requestType)
            ->willReturnSelf();

        $this->assertEquals(
            $this->collectionMock,
            $this->collectionMock->filterByRequestType($requestType)
        );
    }

    /**
     * @return void
     */
    public function testFilterByLifetime()
    {
        $lifetime = 600;
        $timestamp = time();

        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with(
                'created_at',
                ['gt' => $this->collectionMock->getConnection()->formatDate($timestamp - $lifetime)]
            )
            ->willReturnSelf();

        $this->assertEquals(
            $this->collectionMock,
            $this->collectionMock->filterByLifetime($lifetime)
        );
    }

    /**
     * @return void
     */
    public function testFilterLastItem()
    {
        $this->collectionMock->expects($this->once())
            ->method('addOrder')
            ->with('created_at', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('limit')
            ->willReturnSelf();

        $this->assertEquals(
            $this->collectionMock,
            $this->collectionMock->filterLastItem()
        );
    }

    /**
     * @return void
     */
    public function testFilterByIpOrAccountReference()
    {
        $ip = 12345;
        $accountReference = '1234567';

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with(
                ['ip', 'account_reference'],
                [
                    ['eq' => $ip],
                    ['eq' => $accountReference],
                ]
            )
            ->willReturnSelf();

        $this->assertEquals(
            $this->collectionMock,
            $this->collectionMock->filterByIpOrAccountReference($ip, $accountReference)
        );
    }

    /**
     * @return void
     */
    public function testDeleteRecordsOlderThen()
    {
        $timestamp = time();

        $this->resourceMock->expects($this->any())
            ->method('deleteRecordsOlderThen')
            ->with($timestamp);

        $result = $this->collectionMock->deleteRecordsOlderThen($timestamp);
        $this->assertEquals($this->collectionMock, $result);
    }
}
