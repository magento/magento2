<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Resource\Oauth;

/**
 * Unit test for \Magento\Integration\Model\Resource\Oauth\Consumer
 */
class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Integration\Model\Oauth\Consumer
     */
    protected $consumerMock;

    /**
     * @var \Magento\Integration\Model\Resource\Oauth\Consumer
     */
    protected $consumerResource;

    public function setUp()
    {
        $this->consumerMock = $this->getMock(
            'Magento\Integration\Model\Oauth\Consumer',
            ['setUpdatedAt', 'getId'],
            [],
            '',
            false
        );

        $this->adapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $this->resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);

        $contextMock = $this->getMock('Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->consumerResource = new \Magento\Integration\Model\Resource\Oauth\Consumer(
            $contextMock,
            new \Magento\Framework\Stdlib\DateTime()
        );
    }

    public function testBeforeSave()
    {
        $this->consumerMock->expects($this->once())->method('setUpdatedAt');
        $this->assertInstanceOf(
            'Magento\Integration\Model\Resource\Oauth\Consumer',
            $this->consumerResource->_beforeSave($this->consumerMock)
        );
    }

    public function testAfterDelete()
    {
        $this->adapterMock->expects($this->exactly(2))->method('delete');
        $this->assertInstanceOf(
            'Magento\Integration\Model\Resource\Oauth\Consumer',
            $this->consumerResource->_afterDelete($this->consumerMock)
        );
    }

    public function testGetTimeInSecondsSinceCreation()
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->any())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('reset')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('columns')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('where')->will($this->returnValue($selectMock));
        $this->adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchOne');
        $this->consumerResource->getTimeInSecondsSinceCreation(1);
    }
}
