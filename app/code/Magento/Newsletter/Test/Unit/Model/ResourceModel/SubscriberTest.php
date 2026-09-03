<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for @see Subscriber
 */
class SubscriberTest extends TestCase
{
    /**
     * @var Subscriber
     */
    private $model;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $resourceMock = $this->createMock(ResourceConnection::class);
        $resourceMock->method('getConnection')->willReturn($this->connectionMock);
        $resourceMock->method('getTableName')->willReturnArgument(0);
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getResources')->willReturn($resourceMock);
        $this->model = new Subscriber(
            $contextMock,
            $this->createMock(DateTime::class),
            $this->createMock(Random::class),
            null,
            $this->createMock(StoreManagerInterface::class)
        );
    }

    /**
     * The batch loader must issue a single ordered query for all of a customer's rows.
     */
    public function testLoadByCustomerAcrossWebsites(): void
    {
        $customerId = 42;
        $rows = [
            ['subscriber_id' => 1, 'store_id' => 1, 'customer_id' => $customerId],
            ['subscriber_id' => 2, 'store_id' => 5, 'customer_id' => $customerId],
        ];
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from')->willReturnSelf();
        $selectMock->expects($this->once())->method('where')
            ->with('customer_id = ?', $customerId)->willReturnSelf();
        $selectMock->expects($this->once())->method('order')
            ->with('subscriber_id ASC')->willReturnSelf();
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchAll')
            ->with($selectMock)->willReturn($rows);
        $this->assertSame($rows, $this->model->loadByCustomerAcrossWebsites($customerId));
    }
}
