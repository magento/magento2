<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\Enabled;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EnabledTest extends TestCase
{
    /**
     * @var SubscriptionHandler|MockObject
     */
    private $subscriptionHandlerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Value|MockObject
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
        $this->subscriptionHandlerMock = $this->createMock(SubscriptionHandler::class);

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
