<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\NewRelicReporting\Model\Observer\ReportProductDeletedToNewRelic;

/**
 * Class ReportProductDeletedToNewRelicTest
 */
class ReportProductDeletedToNewRelicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportProductDeletedToNewRelic
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
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder('Magento\NewRelicReporting\Model\NewRelicWrapper')
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
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->assertSame(
            $this->model,
            $this->model->execute()
        );
    }

    /**
     * Test case when module is enabled in config
     *
     * @return void
     */
    public function testReportProductDeletedToNewRelic()
    {
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->newRelicWrapper->expects($this->once())
            ->method('addCustomParameter')
            ->willReturn(true);

        $this->assertSame(
            $this->model,
            $this->model->execute()
        );
    }
}
