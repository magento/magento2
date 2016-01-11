<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\ResourceModel\PasswordResetRequestEvent;

/**
 * Test class for \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection testing
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection */
    protected $collectionMock;

    /** @var \Magento\Security\Helper\SecurityConfig */
    protected $securityConfigMock;

    /** @var \Magento\Framework\Stdlib\DateTime */
    protected $dateTimeMock;

    /** @var \Magento\Framework\DB\Select */
    protected $selectMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
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

        $this->securityConfigMock = $this->getMock(
            '\Magento\Security\Helper\SecurityConfig',
            ['getCurrentTimestamp'],
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

        $this->selectMock = $this->getMock(
            '\Magento\Framework\DB\Select',
            ['limit', 'from'],
            [],
            '',
            false
        );

        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($this->selectMock);

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
            '\Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection',
            ['addFieldToFilter', 'addOrder'], //
            [$entityFactory, $logger, $fetchStrategy, $eventManager,
                $this->securityConfigMock, $this->dateTimeMock,
                $connection, $resource],
            '',
            true
        );

        $this->collectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($this->selectMock);
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

        $this->securityConfigMock->expects($this->once())
            ->method('getCurrentTimestamp')
            ->willReturn($timestamp);

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with(
                'created_at',
                ['gt' => $this->dateTimeMock->formatDate($timestamp - $lifetime)]
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
}
