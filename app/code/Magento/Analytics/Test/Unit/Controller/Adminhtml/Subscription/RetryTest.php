<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Controller\Adminhtml\Subscription;

use Magento\Analytics\Controller\Adminhtml\Subscription\Retry;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class RetryTest
 */
class RetryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var SubscriptionHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionHandlerMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Retry
     */
    private $retryController;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriptionHandlerMock = $this->getMockBuilder(SubscriptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->retryController = $this->objectManagerHelper->getObject(
            Retry::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                'subscriptionHandler'  => $this->subscriptionHandlerMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->resultFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock
            ->expects($this->once())
            ->method('setPath')
            ->with('adminhtml')
            ->willReturnSelf();
        $this->subscriptionHandlerMock
            ->expects($this->once())
            ->method('processEnabled')
            ->with()
            ->willReturn(true);
        $this->assertSame(
            $this->resultRedirectMock,
            $this->retryController->execute()
        );
    }

    /**
     * @dataProvider executeExceptionsDataProvider
     *
     * @param \Exception $exception
     * @param Phrase $message
     */
    public function testExecuteWithException(\Exception $exception, Phrase $message)
    {
        $this->resultFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock
            ->expects($this->once())
            ->method('setPath')
            ->with('adminhtml')
            ->willReturnSelf();
        $this->subscriptionHandlerMock
            ->expects($this->once())
            ->method('processEnabled')
            ->with()
            ->willThrowException($exception);
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $message);

        $this->assertSame(
            $this->resultRedirectMock,
            $this->retryController->execute()
        );
    }

    /**
     * @return array
     */
    public function executeExceptionsDataProvider()
    {
        return [
            [new LocalizedException(__('TestMessage')), __('TestMessage')],
            [
                new \Exception('TestMessage'),
                __('Sorry, there has been an error processing your request. Please try again later.')
            ],
        ];
    }
}
