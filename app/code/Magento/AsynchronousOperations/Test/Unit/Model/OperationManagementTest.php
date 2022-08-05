<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\OperationManagement;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OperationManagementTest extends TestCase
{
    /**
     * @var OperationManagement
     */
    private $model;

    /**
     * @var OperationInterfaceFactory|MockObject
     */
    private $operationFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    protected function setUp(): void
    {
        $this->operationFactoryMock = $this->createPartialMock(
            OperationInterfaceFactory::class,
            ['create']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();

        $this->model = new OperationManagement(
            $this->operationFactoryMock,
            $this->loggerMock,
            $this->resourceConnectionMock
        );
    }

    /**
     * Test change operation status.
     */
    public function testChangeOperationStatus()
    {
        $operationKey = 1;
        $status = 1;
        $message = 'Message';
        $data = 'data';
        $errorCode = 101;
        $bulkUuid = '13f85e88-be1d-4ce7-8570-88637a589930';

        $tableName = 'magento_operation';

        $bind = [
            'error_code' => $errorCode,
            'status' => $status,
            'result_message' => $message,
            'serialized_data' => $data,
            'result_serialized_data' => ''
        ];
        $where = ['bulk_uuid = ?' => $bulkUuid, 'operation_key = ?' => $operationKey];

        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')
            ->willReturn($connection);
        $this->resourceConnectionMock->expects($this->once())->method('getTableName')->with($tableName)
            ->willReturn($tableName);

        $connection->expects($this->once())->method('update')->with($tableName, $bind, $where)
            ->willReturn(1);
        $this->assertTrue(
            $this->model->changeOperationStatus($bulkUuid, $operationKey, $status, $errorCode, $message, $data)
        );
    }

    /**
     * Test generic exception throw case.
     */
    public function testChangeOperationStatusIfExceptionWasThrown()
    {
        $operationKey = 1;
        $status = 1;
        $message = 'Message';
        $data = 'data';
        $errorCode = 101;
        $bulkUuid = '13f85e88-be1d-4ce7-8570-88637a589930';

        $tableName = 'magento_operation';

        $bind = [
            'error_code' => $errorCode,
            'status' => $status,
            'result_message' => $message,
            'serialized_data' => $data,
            'result_serialized_data' => ''
        ];
        $where = ['bulk_uuid = ?' => $bulkUuid, 'operation_key = ?' => $operationKey];

        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')
            ->willReturn($connection);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')->with($tableName)
            ->willReturn($tableName);

        $connection->expects($this->once())->method('update')->with($tableName, $bind, $where)
            ->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())->method('critical');
        $this->assertFalse(
            $this->model->changeOperationStatus($bulkUuid, $operationKey, $status, $errorCode, $message, $data)
        );
    }
}
