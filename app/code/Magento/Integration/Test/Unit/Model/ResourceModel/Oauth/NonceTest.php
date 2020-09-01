<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Integration\Model\ResourceModel\Oauth\Nonce;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Oauth\Nonce
 */
class NonceTest extends TestCase
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
     * @var Nonce
     */
    protected $nonceResource;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Mysql::class);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->nonceResource = new Nonce($contextMock);
    }

    public function testDeleteOldEntries()
    {
        $this->connectionMock->expects($this->once())->method('delete');
        $this->connectionMock->expects($this->once())->method('quoteInto');
        $this->nonceResource->deleteOldEntries(5);
    }

    public function testSelectByCompositeKey()
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->exactly(2))->method('where')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchRow');
        $this->nonceResource->selectByCompositeKey('nonce', 5);
    }
}
