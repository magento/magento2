<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\OperationManagement;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
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
     * @var MockObject
     */
    private $entityManagerMock;

    /**
     * @var MockObject
     */
    private $operationFactoryMock;

    /**
     * @var MockObject
     */
    private $operationMock;

    /**
     * @var MockObject
     */
    private $loggerMock;
    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->operationFactoryMock = $this->createPartialMock(
            OperationInterfaceFactory::class,
            ['create']
        );
        $this->operationMock =
            $this->getMockForAbstractClass(OperationInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->model = new OperationManagement(
            $this->entityManagerMock,
            $this->operationFactoryMock,
            $this->loggerMock
        );
    }

    public function testChangeOperationStatus()
    {
        $operationId = 1;
        $status = 1;
        $message = 'Message';
        $data = 'data';
        $errorCode = 101;
        $this->operationFactoryMock->expects($this->once())->method('create')->willReturn($this->operationMock);
        $this->entityManagerMock->expects($this->once())->method('load')->with($this->operationMock, $operationId);
        $this->operationMock->expects($this->once())->method('setStatus')->with($status)->willReturnSelf();
        $this->operationMock->expects($this->once())->method('setResultMessage')->with($message)->willReturnSelf();
        $this->operationMock->expects($this->once())->method('setSerializedData')->with($data)->willReturnSelf();
        $this->operationMock->expects($this->once())->method('setErrorCode')->with($errorCode)->willReturnSelf();
        $this->entityManagerMock->expects($this->once())->method('save')->with($this->operationMock);
        $this->assertTrue($this->model->changeOperationStatus($operationId, $status, $errorCode, $message, $data));
    }

    public function testChangeOperationStatusIfExceptionWasThrown()
    {
        $operationId = 1;
        $status = 1;
        $message = 'Message';
        $data = 'data';
        $errorCode = 101;
        $this->operationFactoryMock->expects($this->once())->method('create')->willReturn($this->operationMock);
        $this->entityManagerMock->expects($this->once())->method('load')->with($this->operationMock, $operationId);
        $this->operationMock->expects($this->once())->method('setStatus')->with($status)->willReturnSelf();
        $this->operationMock->expects($this->once())->method('setResultMessage')->with($message)->willReturnSelf();
        $this->operationMock->expects($this->once())->method('setSerializedData')->with($data)->willReturnSelf();
        $this->operationMock->expects($this->once())->method('setErrorCode')->with($errorCode)->willReturnSelf();
        $this->entityManagerMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())->method('critical');
        $this->assertFalse($this->model->changeOperationStatus($operationId, $status, $errorCode, $message, $data));
    }
}
