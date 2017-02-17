<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Consumer
 */
class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->consumerMock = $this->getMock(
            \Magento\Integration\Model\Oauth\Consumer::class,
            ['setUpdatedAt', 'getId'],
            [],
            '',
            false
        );

        $this->connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [], [], '', false);

        $this->resourceMock = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->getMock(\Magento\Framework\Model\ResourceModel\Db\Context::class, [], [], '', false);
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
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $selectMock->expects($this->any())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('reset')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('columns')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne');
        $this->consumerResource->getTimeInSecondsSinceCreation(1);
    }
}
