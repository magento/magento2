<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Model\Observer\CheckConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckConfigTest extends TestCase
{
    /**
     * @var CheckConfig
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var NewRelicWrapper|MockObject
     */
    protected $newRelicWrapper;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled', 'disableModule'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder(NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExtensionInstalled'])
            ->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new CheckConfig(
            $this->config,
            $this->newRelicWrapper,
            $this->messageManager
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testCheckConfigModuleDisabledFromConfig()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->model->execute($eventObserver);
    }

    /**
     * Test case when module is enabled in config but php extension is not installed
     *
     * @return void
     */
    public function testCheckConfigExtensionNotInstalled()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->newRelicWrapper->expects($this->once())
            ->method('isExtensionInstalled')
            ->willReturn(true);

        $this->model->execute($eventObserver);
    }

    /**
     * Test case when module is enabled in config and php extension is installed
     *
     * @return void
     */
    public function testCheckConfig()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->newRelicWrapper->expects($this->once())
            ->method('isExtensionInstalled')
            ->willReturn(false);
        $this->config->expects($this->once())
            ->method('disableModule');
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $this->model->execute($eventObserver);
    }
}
