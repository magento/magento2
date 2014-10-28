<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $jobList = array(
            'jobname1' => array('instance' => 'TestInstance', 'method' => 'testMethod', 'schedule' => '* * * * *')
        );
        $this->_configData->expects($this->once())->method('getJobs')->will($this->returnValue($jobList));
        $result = $this->_config->getJobs();
        $this->assertEquals($jobList, $result);
    }
}
