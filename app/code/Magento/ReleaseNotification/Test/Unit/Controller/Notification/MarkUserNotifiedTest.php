<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReleaseNotification\Test\Unit\Controller\Notification;

use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Credential\StorageInterface;
use Magento\Backend\Model\Auth;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\ReleaseNotification\Model\ResourceModel\Viewer\Logger as NotificationLogger;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ReleaseNotification\Controller\Adminhtml\Notification\MarkUserNotified;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MarkUserNotifiedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StorageInterface
     */
    private $storageMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Auth
     */
    private $authMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Json
     */
    private $resultMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ProductMetadataInterface
     */
    private $productMetadataMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|NotificationLogger
     */
    private $notificationLoggerMock;

    /**
     * @var MarkUserNotified
     */
    private $action;

    protected function setUp(): void
    {
        $this->storageMock = $this->getMockBuilder(StorageInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->authMock = $this->getMockBuilder(Auth::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())
            ->method('getAuth')
            ->willReturn($this->authMock);
        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->notificationLoggerMock = $this->getMockBuilder(NotificationLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->action = $objectManagerHelper->getObject(
            MarkUserNotified::class,
            [
                'resultFactory' => $resultFactoryMock,
                'productMetadata' => $this->productMetadataMock,
                'notificationLogger' => $this->notificationLoggerMock,
                'context' => $contextMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testExecuteSuccess()
    {
        $this->authMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->storageMock);
        $this->storageMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('999.999.999-alpha');
        $this->notificationLoggerMock->expects($this->once())
            ->method('log')
            ->with(1, '999.999.999-alpha')
            ->willReturn(true);
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'success' => true,
                    'error_message' => ''
                ],
                false,
                []
            )->willReturnSelf();
        $this->assertEquals($this->resultMock, $this->action->execute());
    }

    public function testExecuteFailedWithLocalizedException()
    {
        $this->authMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->storageMock);
        $this->storageMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('999.999.999-alpha');
        $this->notificationLoggerMock->expects($this->once())
            ->method('log')
            ->willThrowException(new LocalizedException(__('Error message')));
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'success' => false,
                    'error_message' => 'Error message'
                ],
                false,
                []
            )->willReturnSelf();
        $this->assertEquals($this->resultMock, $this->action->execute());
    }

    public function testExecuteFailedWithException()
    {
        $this->authMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->storageMock);
        $this->storageMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('999.999.999-alpha');
        $this->notificationLoggerMock->expects($this->once())
            ->method('log')
            ->willThrowException(new \Exception('Any message'));
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'success' => false,
                    'error_message' => __('It is impossible to log user action')
                ],
                false,
                []
            )->willReturnSelf();
        $this->assertEquals($this->resultMock, $this->action->execute());
    }
}
