<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\NewRelicReporting\Model\Observer\ReportSystemCacheFlush;

/**
 * Class ReportSystemCacheFlushTest
 */
class ReportSystemCacheFlushTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportSystemCacheFlush
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\SystemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\System|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemModel;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoder;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->systemFactory = $this->getMockBuilder(\Magento\NewRelicReporting\Model\SystemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->systemModel = $this->getMockBuilder(\Magento\NewRelicReporting\Model\System::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoder = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->getMock();
        $this->systemFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->systemModel);

        $this->model = new ReportSystemCacheFlush(
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
    public function testReportSystemCacheFlushModuleDisabledFromConfig()
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
     * Test case when module is enabled in config
     *
     * @return void
     */
    public function testReportSystemCacheFlush()
    {
        $testType = 'systemCacheFlush';
        $testAction = 'JSON string';

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
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
