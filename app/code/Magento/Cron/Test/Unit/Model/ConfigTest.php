<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model;

use Magento\Cron\Model\Config;
use Magento\Cron\Model\Config\Data as ConfigDataModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var ConfigDataModel|MockObject
     */
    private $configDataMock;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->configDataMock = $this->getMockBuilder(
            ConfigDataModel::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->config = new Config($this->configDataMock);
    }

    public function testGetJobsReturnsOriginalConfigData()
    {
        $jobList = [
            'jobname1' => ['instance' => 'TestInstance', 'method' => 'testMethod', 'schedule' => '* * * * *'],
        ];
        $this->configDataMock->expects($this->once())
            ->method('getJobs')
            ->willReturn($jobList);
        $result = $this->config->getJobs();
        $this->assertEquals($jobList, $result);
    }
}
