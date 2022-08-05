<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\Framework\Json\EncoderInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Cron\ReportModulesInfo;
use Magento\NewRelicReporting\Model\Module\Collect;
use Magento\NewRelicReporting\Model\System;
use Magento\NewRelicReporting\Model\SystemFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportModulesInfoTest extends TestCase
{
    /**
     * @var ReportModulesInfo
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var Collect|MockObject
     */
    protected $collectMock;

    /**
     * @var SystemFactory|MockObject
     */
    protected $systemFactoryMock;

    /**
     * @var System|MockObject
     */
    protected $systemModelMock;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoderMock;

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
        $this->collectMock = $this->getMockBuilder(Collect::class)
            ->disableOriginalConstructor()
            ->setMethods(['getModuleData'])
            ->getMock();
        $this->systemFactoryMock = $this->getMockBuilder(SystemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->systemModelMock = $this->getMockBuilder(System::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoderMock = $this->getMockBuilder(EncoderInterface::class)
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
