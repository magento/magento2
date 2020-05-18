<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Integration\Model\Oauth\Consumer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Consumer
 */
class ConsumerTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Consumer
     */
    protected $consumerMock;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Consumer
     */
    protected $consumerResource;

    protected function setUp(): void
    {
        $this->consumerMock = $this->getMockBuilder(Consumer::class)
            ->addMethods(['setUpdatedAt'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->createMock(Mysql::class);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->consumerResource = new \Magento\Integration\Model\ResourceModel\Oauth\Consumer(
            $contextMock,
            new DateTime()
        );
    }

    public function testAfterDelete()
    {
        $this->connectionMock->expects($this->exactly(2))->method('delete');
        $this->assertInstanceOf(
            \Magento\Integration\Model\ResourceModel\Oauth\Consumer::class,
            $this->consumerResource->_afterDelete($this->consumerMock)
        );
    }

    public function testGetTimeInSecondsSinceCreation()
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('reset')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('columns')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('where')->willReturn($selectMock);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne');
        $this->consumerResource->getTimeInSecondsSinceCreation(1);
    }
}
