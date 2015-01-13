<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model;

/**
 * Class \Magento\Cron\Model\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Model\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configData;

    /**
     * @var \Magento\Cron\Model\Config
     */
    protected $_config;

    /**
     * Prepare data
     */
    protected function setUp()
    {
        $this->_configData = $this->getMockBuilder(
            'Magento\Cron\Model\Config\Data'
        )->disableOriginalConstructor()->getMock();
        $this->_config = new \Magento\Cron\Model\Config($this->_configData);
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
