<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Model\ResourceModel\Oauth\Token;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Token
 */
class TokenTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Token
     */
    protected $tokenResource;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->connectionMock = $this->createMock(Mysql::class);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->tokenResource = $objectManager->getObject(
            Token::class,
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
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByType(5, 'nonce');
    }

    public function testSelectTokenByConsumerIdAndUserType()
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByConsumerIdAndUserType(5, 'nonce');
    }

    public function testSelectTokenByAdminId()
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByAdminId(5);
    }

    public function testSelectTokenByCustomerId()
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from')->will($this->returnValue($selectMock));
        $selectMock->expects($this->exactly(2))->method('where')->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->tokenResource->selectTokenByCustomerId(5);
    }
}
