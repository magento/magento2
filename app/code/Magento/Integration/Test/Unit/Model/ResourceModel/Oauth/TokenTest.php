<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Token
 */
class TokenTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Token
     */
    protected $tokenResource;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [], [], '', false);

        $this->resourceMock = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->getMock(\Magento\Framework\Model\ResourceModel\Db\Context::class, [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->tokenResource = $objectManager->getObject(
            \Magento\Integration\Model\ResourceModel\Oauth\Token::class,
            ['context' => $contextMock]
        );
    }

    public function testCleanOldAuthorizedTokensExcept()
    {
        $tokenMock = $this->getMock(
            \Magento\Integration\Model\Oauth\Token::class,
            ['getId', 'getAuthorized', 'getConsumerId', 'getCustomerId', 'getAdminId'],
            [],
            '',
            false
        );
        $tokenMock->expects($this->any())->method('getId')->willReturn(1);
        $tokenMock->expects($this->once())->method('getAuthorized')->willReturn(true);
        $tokenMock->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->connectionMock->expects($this->any())->method('quoteInto');
        $this->connectionMock->expects($this->once())->method('delete');
        $this->tokenResource->cleanOldAuthorizedTokensExcept($tokenMock);
    }

    public function testDeleteOldEntries()
    {
        $this->connectionMock->expects($this->once())->method('delete');
        $this->connectionMock->expects($this->once())->method('quoteInto');
        $this->tokenResource->deleteOldEntries(5);
    }

    public function testSelectTokenByType()
    {
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByType(5, 'nonce');
    }

    public function testSelectTokenByConsumerIdAndUserType()
    {
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByConsumerIdAndUserType(5, 'nonce');
    }

    public function testSelectTokenByAdminId()
    {
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByAdminId(5);
    }

    public function testSelectTokenByCustomerId()
    {
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByCustomerId(5);
    }
}
