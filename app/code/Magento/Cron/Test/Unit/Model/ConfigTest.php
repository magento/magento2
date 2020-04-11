<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model;

use Magento\Cron\Model\Config;
use Magento\Cron\Model\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $_configData;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * Prepare data
     */
    protected function setUp(): void
    {
        $this->_configData = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()->getMock();
        $this->_config = new Config($this->_configData);
    }

    /**
     * Test method call
     */
    public function testGetJobs()
    {
        $jobList = [
            'jobname1' => ['instance' => 'TestInstance', 'method' => 'testMethod', 'schedule' => '* * * * *'],
        ];
        $this->_configData->expects($this->once())->method('getJobs')->will($this->returnValue($jobList));
        $result = $this->_config->getJobs();
        $this->assertEquals($jobList, $result);
    }
}
