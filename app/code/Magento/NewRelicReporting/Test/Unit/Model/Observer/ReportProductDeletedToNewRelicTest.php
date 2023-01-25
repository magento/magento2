<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Model\Observer\ReportProductDeletedToNewRelic;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportProductDeletedToNewRelicTest extends TestCase
{
    /**
     * @var ReportProductDeletedToNewRelic
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
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder(NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCustomParameter'])
            ->getMock();

        $this->model = new ReportProductDeletedToNewRelic(
            $this->config,
            $this->newRelicWrapper
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportProductDeletedToNewRelicModuleDisabledFromConfig()
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
     * Test case when module is enabled in config
     *
     * @return void
     */
    public function testReportProductDeletedToNewRelic()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->newRelicWrapper->expects($this->once())
            ->method('addCustomParameter')
            ->willReturn(true);

        $this->model->execute($eventObserver);
    }
}
