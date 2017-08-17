<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\NewRelicReporting\Model\Cron\ReportModulesInfo;

/**
 * Class ReportModulesInfoTest
 */
class ReportModulesInfoTest extends \PHPUnit\Framework\TestCase
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
    protected $collectMock;

    /**
     * @var \Magento\NewRelicReporting\Model\SystemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemFactoryMock;

    /**
     * @var \Magento\NewRelicReporting\Model\System|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemModelMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

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
        $this->collectMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Module\Collect::class)
            ->disableOriginalConstructor()
            ->setMethods(['getModuleData'])
            ->getMock();
        $this->systemFactoryMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\SystemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->systemModelMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\System::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->getMock();

        $this->systemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->systemModelMock);

        $this->jsonEncoderMock->expects($this->any())
            ->method('encode')
            ->willReturn('json_string');

        $this->model = new ReportModulesInfo(
            $this->config,
            $this->collectMock,
            $this->systemFactoryMock,
            $this->jsonEncoderMock
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
        $this->collectMock->expects($this->once())
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
        $this->systemModelMock->expects($this->any())->method('setData')->willReturnSelf();
        $this->systemModelMock->expects($this->any())->method('save')->willReturnSelf();

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }
}
