<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\NewRelicReporting\Model\Observer\CheckConfig;

/**
 * Class CheckConfigTest
 */
class CheckConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckConfig
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\NewRelicWrapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $newRelicWrapper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled', 'disableModule'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder(\Magento\NewRelicReporting\Model\NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExtensionInstalled'])
            ->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
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
            ->method('addError');

        $this->model->execute($eventObserver);
    }
}
