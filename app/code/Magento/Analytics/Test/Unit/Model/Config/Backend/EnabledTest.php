<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\Enabled;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Psr\Log\LoggerInterface;

class EnabledTest extends \PHPUnit\Framework\TestCase
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
     * @var Value|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Enabled
     */
    private $enabledModel;

    /**
     * @var int
     */
    private $valueEnabled = 1;

    /**
     * @var int
     */
    private $valueDisabled = 0;

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

        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->enabledModel = $this->objectManagerHelper->getObject(
            Enabled::class,
            [
                'subscriptionHandler' => $this->subscriptionHandlerMock,
                '_logger' => $this->loggerMock,
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testAfterSaveSuccessEnabled()
    {
        $this->enabledModel->setData('value', $this->valueEnabled);

        $this->configMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn(!$this->valueEnabled);

        $this->subscriptionHandlerMock
            ->expects($this->once())
            ->method('processEnabled')
            ->with()
            ->willReturn(true);

        $this->assertInstanceOf(
            Value::class,
            $this->enabledModel->afterSave()
        );
    }

    /**
     * @return void
     */
    public function testAfterSaveSuccessDisabled()
    {
        $this->enabledModel->setData('value', $this->valueDisabled);

        $this->configMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn(!$this->valueDisabled);

        $this->subscriptionHandlerMock
            ->expects($this->once())
            ->method('processDisabled')
            ->with()
            ->willReturn(true);

        $this->assertInstanceOf(
            Value::class,
            $this->enabledModel->afterSave()
        );
    }

    /**
     * @return void
     */
    public function testAfterSaveSuccessValueNotChanged()
    {
        $this->enabledModel->setData('value', null);

        $this->configMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn(null);

        $this->subscriptionHandlerMock
            ->expects($this->never())
            ->method('processEnabled')
            ->with()
            ->willReturn(true);
        $this->subscriptionHandlerMock
            ->expects($this->never())
            ->method('processDisabled')
            ->with()
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
        $this->enabledModel->setData('value', $this->valueEnabled);

        $this->subscriptionHandlerMock
            ->expects($this->once())
            ->method('processEnabled')
            ->with()
            ->willThrowException($exception);

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->enabledModel->afterSave();
    }
}
