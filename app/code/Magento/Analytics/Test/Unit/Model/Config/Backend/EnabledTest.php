<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\Enabled;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Psr\Log\LoggerInterface;

class EnabledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubscriptionHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionHandlerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Enabled
     */
    private $enabledModel;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->subscriptionHandlerMock = $this->getMockBuilder(SubscriptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->enabledModel = $this->objectManagerHelper->getObject(
            Enabled::class,
            [
                'subscriptionHandler' => $this->subscriptionHandlerMock,
                '_logger' => $this->loggerMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testAfterSaveSuccess()
    {
        $this->subscriptionHandlerMock
            ->expects($this->once())
            ->method('process')
            ->with($this->enabledModel)
            ->willReturn(true);

        $this->assertInstanceOf(
            Value::class,
            $this->enabledModel->afterSave()
        );
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteAfterSaveFailedWithLocalizedException()
    {
        $exception = new \Exception('Message');

        $this->subscriptionHandlerMock
            ->expects($this->once())
            ->method('process')
            ->with($this->enabledModel)
            ->willThrowException($exception);

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->enabledModel->afterSave();
    }
}
