<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Controller\Adminhtml\Subscription;

use Magento\Analytics\Controller\Adminhtml\Subscription\Postpone;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Intl\DateTimeFactory;
use Psr\Log\LoggerInterface;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class PostponeTest
 */
class PostponeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DateTimeFactory
     */
    private $dateTimeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\DateTime
     */
    private $dateTimeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|NotificationTime
     */
    private $notificationTimeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResultFactory
     */
    private $resultFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Json
     */
    private $resultMock;

    /**
     * @var Postpone
     */
    private $action;

    public function setUp()
    {
        $this->dateTimeFactoryMock = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->notificationTimeMock = $this->getMockBuilder(NotificationTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->action = $objectManagerHelper->getObject(
            Postpone::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                'dateTimeFactory' => $this->dateTimeFactoryMock,
                'notificationTime' => $this->notificationTimeMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testExecuteSuccess()
    {
        $this->dateTimeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dateTimeMock);
        $this->dateTimeMock->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(100500);
        $this->notificationTimeMock->expects($this->once())
            ->method('storeLastTimeNotification')
            ->with(100500)
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
        $this->dateTimeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dateTimeMock);
        $this->dateTimeMock->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(100500);
        $this->notificationTimeMock->expects($this->once())
            ->method('storeLastTimeNotification')
            ->with(100500)
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
        $this->dateTimeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dateTimeMock);
        $this->dateTimeMock->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(100500);
        $this->notificationTimeMock->expects($this->once())
            ->method('storeLastTimeNotification')
            ->with(100500)
            ->willThrowException(new \Exception('Any message'));
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'success' => false,
                    'error_message' => __('Error occurred during postponement notification')
                ],
                false,
                []
            )->willReturnSelf();
        $this->assertEquals($this->resultMock, $this->action->execute());
    }
}
