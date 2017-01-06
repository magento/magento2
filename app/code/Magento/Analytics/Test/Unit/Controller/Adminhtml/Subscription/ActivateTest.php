<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Controller\Adminhtml\Subscription;

use Magento\Analytics\Controller\Adminhtml\Subscription\Activate;
use Magento\Analytics\Model\Subscription;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Psr\Log\LoggerInterface;

class ActivateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonMock;

    /**
     * @var Subscription|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionModelMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Activate
     */
    private $activateController;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriptionModelMock = $this->getMockBuilder(Subscription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->activateController = $this->objectManagerHelper->getObject(
            Activate::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                'subscription'  => $this->subscriptionModelMock,
                'logger' => $this->loggerMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteSuccess()
    {
        $successResult = [
            'success' => true,
            'error_message' => '',
        ];

        $this->subscriptionModelMock
            ->expects($this->once())
            ->method('enable')
            ->willReturn(true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($successResult)
            ->willReturnSelf();
        $this->assertSame(
            $this->resultJsonMock,
            $this->activateController->execute()
        );
    }

    /**
     * @dataProvider executeExeptionsDataProvider
     *
     * @param \Exception $exception
     */
    public function testExecuteWithException(\Exception $exception)
    {
        $this->subscriptionModelMock
            ->expects($this->once())
            ->method('enable')
            ->willThrowException($exception);
        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());
        $this->resultFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultJsonMock);

        if ($exception instanceof LocalizedException) {
            $this->resultJsonMock
                ->expects($this->once())
                ->method('setData')
                ->with([
                    'success' => false,
                    'error_message' => $exception->getMessage(),
                ])
                ->willReturnSelf();
        } else {
            $this->resultJsonMock
                ->expects($this->once())
                ->method('setData')
                ->withAnyParameters()
                ->willReturnSelf();
        }

        $this->assertSame(
            $this->resultJsonMock,
            $this->activateController->execute()
        );
    }

    /**
     * @return array
     */
    public function executeExeptionsDataProvider()
    {
        return [
            [new LocalizedException(new Phrase('TestMessage'))],
            [new \Exception('TestMessage')],
        ];
    }
}
