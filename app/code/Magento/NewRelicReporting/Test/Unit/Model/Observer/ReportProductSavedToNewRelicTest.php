<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Model\Observer\ReportProductSavedToNewRelic;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportProductSavedToNewRelicTest extends TestCase
{
    /**
     * @var ReportProductSavedToNewRelic
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
            ->onlyMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder(NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addCustomParameter'])
            ->getMock();

        $this->model = new ReportProductSavedToNewRelic(
            $this->config,
            $this->newRelicWrapper
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportProductSavedToNewRelicModuleDisabledFromConfig()
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
    public function testReportProductSavedToNewRelic()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder(Event::class)
            ->addMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $this->newRelicWrapper->expects($this->once())
            ->method('addCustomParameter')
            ->willReturn(true);

        $this->model->execute($eventObserver);
    }

    /**
     * Test case when module is enabled in config and product updating
     *
     * @dataProvider actionDataProvider
     * @return void
     */
    public function testReportProductUpdatedToNewRelic($isNewObject)
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder(Event::class)
            ->addMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())
            ->method('isObjectNew')
            ->willReturn($isNewObject);
        $event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $this->newRelicWrapper->expects($this->once())
            ->method('addCustomParameter')
            ->willReturn(true);

        $this->model->execute($eventObserver);
    }

    /**
     * @return array
     */
    public static function actionDataProvider()
    {
        return [[true], [false]];
    }
}
