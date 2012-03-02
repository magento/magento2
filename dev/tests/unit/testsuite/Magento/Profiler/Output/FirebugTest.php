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
 * @category    Magento
 * @package     Magento_Profiler
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test case for Magento_Profiler_Output_Firebug
 *
 * @group profiler
 */
class Magento_Profiler_Output_FirebugTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Profiler_Output_Firebug|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var Zend_Controller_Response_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    public static function setUpBeforeClass()
    {
        Magento_Profiler::enable();
        /* Profiler measurements fixture */
        $timersProperty = new ReflectionProperty('Magento_Profiler', '_timers');
        $timersProperty->setAccessible(true);
        $timersProperty->setValue(include __DIR__ . '/../_files/timers.php');
        $timersProperty->setAccessible(false);
    }

    public static function tearDownAfterClass()
    {
        Magento_Profiler::reset();
    }

    protected function setUp()
    {
        $this->_object = $this->getMock('Magento_Profiler_Output_Firebug', array('_renderCaption'));
        $this->_object
            ->expects($this->any())
            ->method('_renderCaption')
            ->will($this->returnValue('Code Profiler Title'))
        ;
        $this->_response = $this->getMock('Zend_Controller_Response_Http', array('canSendHeaders', 'sendHeaders'));
        $this->_response
            ->expects($this->any())
            ->method('canSendHeaders')
            ->will($this->returnValue(true))
        ;
        $this->_object->setResponse($this->_response);
    }

    public function testDisplay()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 with FirePHP/1.6';
        $this->_response
            ->expects($this->atLeastOnce())
            ->method('sendHeaders')
        ;
        $this->_object->display();
        $actualHeaders = $this->_response->getHeaders();

        $this->assertNotEmpty($actualHeaders);

        $actualProtocol = false;
        $actualProfilerData = false;
        foreach ($actualHeaders as $oneHeader) {
            $headerName = $oneHeader['name'];
            $headerValue = $oneHeader['value'];
            if (!$actualProtocol && ($headerName == 'X-Wf-Protocol-1')) {
                $actualProtocol = $headerValue;
            }
            if (!$actualProfilerData && ($headerName == 'X-Wf-1-1-1-1')) {
                $actualProfilerData = $headerValue;
            }
        }
        $this->assertContains('Protocol/JsonStream', $actualProtocol);
        $this->assertContains('"Type":"TABLE","Label":"Code Profiler Title"', $actualProfilerData);
        $this->assertContains('['
            . '["Timer Id","Time","Avg","Cnt","Emalloc","RealMem"],'
            . '["some_root_timer","0.080000","0.040000","2","51,000,000","50,000,000"],'
            . '[". some_nested_timer","0.080000","0.026667","3","42,000,000","40,000,000"],'
            . '[". . some_deeply_nested_timer","0.030000","0.010000","3","13,000,000","10,000,000"],'
            . '["one_more_root_timer","0.010000","0.010000","1","23,456,789","12,345,678"]]',
            $actualProfilerData
        );
    }
}
