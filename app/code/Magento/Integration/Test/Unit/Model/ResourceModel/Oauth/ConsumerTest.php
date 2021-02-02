<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Consumer
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Integration\Model\Oauth\Consumer
     */
    protected $consumerMock;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Consumer
     */
    protected $consumerResource;

    protected function setUp(): void
    {
        $this->consumerMock = $this->createPartialMock(
            \Magento\Integration\Model\Oauth\Consumer::class,
            ['setUpdatedAt', 'getId']
        );

        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);

        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->consumerResource = new \Magento\Integration\Model\ResourceModel\Oauth\Consumer(
            $contextMock,
            new \Magento\Framework\Stdlib\DateTime()
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
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('reset')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('columns')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('where')->willReturn($selectMock);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne');
        $this->consumerResource->getTimeInSecondsSinceCreation(1);
    }
}
