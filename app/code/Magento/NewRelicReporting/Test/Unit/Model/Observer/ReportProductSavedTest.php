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
use Magento\Framework\Json\EncoderInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Observer\ReportProductSaved;
use Magento\NewRelicReporting\Model\System;
use Magento\NewRelicReporting\Model\SystemFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportProductSavedTest extends TestCase
{
    /**
     * @var ReportProductSaved
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var SystemFactory|MockObject
     */
    protected $systemFactory;

    /**
     * @var System|MockObject
     */
    protected $systemModel;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoder;

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
        $this->systemFactory = $this->getMockBuilder(SystemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->systemModel = $this->getMockBuilder(System::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->getMock();
        $this->systemFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->systemModel);

        $this->model = new ReportProductSaved(
            $this->config,
            $this->systemFactory,
            $this->jsonEncoder
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportProductSavedModuleDisabledFromConfig()
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
    public function testReportProductSaved()
    {
        $testType = 'adminProductChange';
        $testAction = 'JSON string';

        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder(Event::class)
            ->setMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        /** @var Product|MockObject $product */
        $product = $this->getMockBuilder(Product::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->isObjectNew(true);
        $event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($testAction);
        $this->systemModel->expects($this->once())
            ->method('setData')
            ->with(['type' => $testType, 'action' => $testAction])
            ->willReturnSelf();
        $this->systemModel->expects($this->once())
            ->method('save');

        $this->model->execute($eventObserver);
    }

    /**
     * Test case when module is enabled in config and product updated
     *
     * @return void
     */
    public function testReportProductUpdated()
    {
        $testType = 'adminProductChange';
        $testAction = 'JSON string';

        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder(Event::class)
            ->setMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        /** @var Product|MockObject $product */
        $product = $this->getMockBuilder(Product::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->isObjectNew(false);
        $event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($testAction);
        $this->systemModel->expects($this->once())
            ->method('setData')
            ->with(['type' => $testType, 'action' => $testAction])
            ->willReturnSelf();
        $this->systemModel->expects($this->once())
            ->method('save');

        $this->model->execute($eventObserver);
    }
}
