<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Integration
 */
class IntegrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Integration
     */
    protected $integrationResourceModel;

    protected function setUp()
    {
        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->selectMock->expects($this->any())->method('from')->will($this->returnValue($this->selectMock));
        $this->selectMock->expects($this->any())->method('where')->will($this->returnValue($this->selectMock));

        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $this->contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $this->contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->integrationResourceModel = new \Magento\Integration\Model\ResourceModel\Integration($this->contextMock);
    }

    public function testSelectActiveIntegrationByConsumerId()
    {
        $consumerId = 1;
        $this->connectionMock->expects($this->once())->method('fetchRow')->with($this->selectMock);
        $this->integrationResourceModel->selectActiveIntegrationByConsumerId($consumerId);
    }
}
