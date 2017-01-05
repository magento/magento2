<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Model;

/**
 * Class OperationManagementTest
 */
class OperationManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\OperationManagement
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;
    
    protected function setUp()
    {
        $this->entityManagerMock = $this->getMock(
            \Magento\Framework\EntityManager\EntityManager::class,
            [],
            [],
            '',
            false
        );
        $this->metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $this->operationFactoryMock = $this->getMock(
            \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->operationMock = $this->getMock(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class);
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
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
