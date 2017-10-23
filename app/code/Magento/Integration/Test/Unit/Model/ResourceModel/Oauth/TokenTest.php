<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Token
 */
class TokenTest extends \PHPUnit\Framework\TestCase
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
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);

        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->tokenResource = $objectManager->getObject(
            \Magento\Integration\Model\ResourceModel\Oauth\Token::class,
            ['context' => $contextMock]
        );
    }

    public function testCleanOldAuthorizedTokensExcept()
    {
        $tokenMock = $this->createPartialMock(
            \Magento\Integration\Model\Oauth\Token::class,
            ['getId', 'getAuthorized', 'getConsumerId', 'getCustomerId', 'getAdminId']
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
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByType(5, 'nonce');
    }

    public function testSelectTokenByConsumerIdAndUserType()
    {
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByConsumerIdAndUserType(5, 'nonce');
    }

    public function testSelectTokenByAdminId()
    {
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByAdminId(5);
    }

    public function testSelectTokenByCustomerId()
    {
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByCustomerId(5);
    }
}
