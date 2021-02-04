<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Model;

/**
 * Class OperationManagementTest
 * Test for OperationManagement
 */
class OperationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\OperationManagement
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $entityManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $operationFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $operationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(\Magento\Framework\EntityManager\EntityManager::class);
        $this->metadataPoolMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $this->operationFactoryMock = $this->createPartialMock(
            \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory::class,
            ['create']
        );
        $this->operationMock =
            $this->createMock(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->model = new \Magento\AsynchronousOperations\Model\OperationManagement(
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
