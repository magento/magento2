<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\LoginAsCustomer\Model\ResourceModel\DeleteAuthenticationDataForCustomer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for DeleteAuthenticationDataForCustomer
 */
class DeleteAuthenticationDataForCustomerTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var DeleteAuthenticationDataForCustomer
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->connection = $this->createMock(AdapterInterface::class);

        $this->resourceConnection
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->model = new DeleteAuthenticationDataForCustomer($this->resourceConnection);
    }

    /**
     * Test successful deletion of authentication data for customer
     */
    public function testExecuteDeletesAuthenticationData(): void
    {
        $secret = 'test-secret-123';
        $tableName = 'login_as_customer';

        $this->resourceConnection
            ->expects($this->once())
            ->method('getTableName')
            ->with('login_as_customer')
            ->willReturn($tableName);

        $this->connection
            ->expects($this->once())
            ->method('delete')
            ->with(
                $tableName,
                ['secret = ?' => $secret]
            );

        $this->model->execute($secret);
    }
}
