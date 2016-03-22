<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Nonce
 */
class NonceTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Nonce
     */
    protected $nonceResource;

    protected function setUp()
    {
        $this->connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $this->resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->nonceResource = new \Magento\Integration\Model\ResourceModel\Oauth\Nonce($contextMock);
    }

    public function testDeleteOldEntries()
    {
        $this->connectionMock->expects($this->once())->method('delete');
        $this->connectionMock->expects($this->once())->method('quoteInto');
        $this->nonceResource->deleteOldEntries(5);
    }

    public function testSelectByCompositeKey()
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->nonceResource->selectByCompositeKey('nonce', 5);
    }
}
