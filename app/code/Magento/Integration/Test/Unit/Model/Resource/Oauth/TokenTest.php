<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Resource\Oauth;

/**
 * Unit test for \Magento\Integration\Model\Resource\Oauth\Token
 */
class TokenTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Integration\Model\Resource\Oauth\Token
     */
    protected $tokenResource;

    public function setUp()
    {
        $this->adapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $this->resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->tokenResource = new \Magento\Integration\Model\Resource\Oauth\Token(
            $contextMock,
            new \Magento\Framework\Stdlib\DateTime()
        );
    }

    public function testCleanOldAuthorizedTokensExcept()
    {
        $tokenMock = $this->getMock(
            'Magento\Integration\Model\Oauth\Token',
            ['getId', 'getAuthorized', 'getConsumerId', 'getCustomerId', 'getAdminId'],
            [],
            '',
            false
        );
        $tokenMock->expects($this->any())->method('getId')->willReturn(1);
        $tokenMock->expects($this->once())->method('getAuthorized')->willReturn(true);
        $tokenMock->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->adapterMock->expects($this->any())->method('quoteInto');
        $this->adapterMock->expects($this->once())->method('delete');
        $this->tokenResource->cleanOldAuthorizedTokensExcept($tokenMock);
    }

    public function testDeleteOldEntries()
    {
        $this->adapterMock->expects($this->once())->method('delete');
        $this->adapterMock->expects($this->once())->method('quoteInto');
        $this->tokenResource->deleteOldEntries(5);
    }

    public function testSelectTokenByType()
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByType(5, 'nonce');
    }

    public function testSelectTokenByConsumerIdAndUserType()
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByConsumerIdAndUserType(5, 'nonce');
    }

    public function testSelectTokenByAdminId()
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByAdminId(5);
    }

    public function testSelectTokenByCustomerId()
    {
        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByCustomerId(5);
    }
}
