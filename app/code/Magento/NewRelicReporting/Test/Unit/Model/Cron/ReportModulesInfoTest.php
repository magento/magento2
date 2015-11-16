<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\NewRelicReporting\Model\Cron\ReportModulesInfo;

/**
 * Class ReportModulesInfoTest
 */
class ReportModulesInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportModulesInfo
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\Module\Collect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collect;

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
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

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
        $this->collect = $this->getMockBuilder('Magento\NewRelicReporting\Model\Module\Collect')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleData'])
            ->getMock();
        $this->systemFactory = $this->getMockBuilder('Magento\NewRelicReporting\Model\SystemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->systemModel = $this->getMockBuilder('Magento\NewRelicReporting\Model\System')
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoder = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->getMock();
        $this->dateTime = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->setMethods(['formatDate'])
            ->getMock();

        $this->systemFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->systemModel);

        $this->jsonEncoder->expects($this->any())
            ->method('encode')
            ->willReturn('json_string');

        $this->dateTime->expects($this->any())
            ->method('formatDate')
            ->willReturn('1970-01-01 00:00:00');

        $this->model = new ReportModulesInfo(
            $this->config,
            $this->collect,
            $this->systemFactory,
            $this->jsonEncoder,
            $this->dateTime
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportModulesInfoModuleDisabledFromConfig()
    {
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }

    /**
     * Test case when module is enabled
     *
     * @return void
     */
    public function testReportReportModulesInfo()
    {
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->collect->expects($this->once())
            ->method('getModuleData')
            ->willReturn([
                'installed' => '1',
                'uninstalled' => '1',
                'enabled' => '1',
                'disabled' => '1',
                'changes' => [
                    ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'enabled'],
                    ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'disabled'],
                    ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'installed'],
                    ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'uninstalled'],
                ]
            ]);
        $this->systemModel->expects($this->any())->method('setData')->willReturnSelf();
        $this->systemModel->expects($this->any())->method('save')->willReturnSelf();

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }
}
