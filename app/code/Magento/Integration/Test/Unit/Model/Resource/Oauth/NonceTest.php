<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Resource\Oauth;

/**
 * Unit test for \Magento\Integration\Model\Resource\Oauth\Nonce
 */
class NonceTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Integration\Model\Resource\Oauth\Nonce
     */
    protected $nonceResource;

    public function setUp()
    {
        $this->adapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $this->resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->nonceResource = new \Magento\Integration\Model\Resource\Oauth\Nonce($contextMock);
    }

    public function testDeleteOldEntries()
    {
        $this->adapterMock->expects($this->once())->method('delete');
        $this->adapterMock->expects($this->once())->method('quoteInto');
        $this->nonceResource->deleteOldEntries(5);
    }

    public function testSelectByCompositeKey()
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchRow');
        $this->nonceResource->selectByCompositeKey('nonce', 5);
    }
}
