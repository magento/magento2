<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Resource;

/**
 * Unit test for \Magento\Integration\Model\Resource\Integration
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Integration\Model\Resource\Integration
     */
    protected $integrationResourceModel;

    protected function setUp()
    {
        $this->selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->selectMock->expects($this->any())->method('from')->will($this->returnValue($this->selectMock));
        $this->selectMock->expects($this->any())->method('where')->will($this->returnValue($this->selectMock));

        $this->adapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $this->adapterMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);

        $this->contextMock = $this->getMock('Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $this->contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->integrationResourceModel = new \Magento\Integration\Model\Resource\Integration($this->contextMock);
    }

    public function testSelectActiveIntegrationByConsumerId()
    {
        $consumerId = 1;
        $this->adapterMock->expects($this->once())->method('fetchRow')->with($this->selectMock);
        $this->integrationResourceModel->selectActiveIntegrationByConsumerId($consumerId);
    }
}
