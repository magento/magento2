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
     * @var SubscriptionHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subscriptionHandlerMock;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var Value|\PHPUnit\Framework\MockObject\MockObject
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
    protected function setUp(): void
    {
        $this->subscriptionHandlerMock = $this->getMockBuilder(SubscriptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
     */
    public function testExecuteAfterSaveFailedWithLocalizedException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

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
