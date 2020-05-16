<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Integration\Model\ResourceModel\Integration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Integration
 */
class IntegrationTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $selectMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Integration
     */
    protected $integrationResourceModel;

    protected function setUp(): void
    {
        $this->selectMock = $this->createMock(Select::class);
        $this->selectMock->expects($this->any())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->any())->method('where')->willReturn($this->selectMock);

        $this->connectionMock = $this->createMock(Mysql::class);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->integrationResourceModel = new Integration($this->contextMock);
    }

    public function testSelectActiveIntegrationByConsumerId()
    {
        $consumerId = 1;
        $this->connectionMock->expects($this->once())->method('fetchRow')->with($this->selectMock);
        $this->integrationResourceModel->selectActiveIntegrationByConsumerId($consumerId);
    }
}
