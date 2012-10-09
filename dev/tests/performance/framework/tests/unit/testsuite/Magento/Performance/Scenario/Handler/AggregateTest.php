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
 * @package     performance_tests
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Performance_Scenario_Handler_AggregateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Scenario_Handler_Aggregate
     */
    protected $_object;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handleOne;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handleTwo;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handleThree;

    protected function setUp()
    {
        $this->_handleOne   = $this->getMock('Magento_Performance_Scenario_HandlerInterface');
        $this->_handleTwo   = $this->getMock('Magento_Performance_Scenario_HandlerInterface');
        $this->_handleThree = $this->getMock('Magento_Performance_Scenario_HandlerInterface');
        $this->_object = new Magento_Performance_Scenario_Handler_Aggregate(array(
            $this->_handleOne, $this->_handleTwo, $this->_handleThree
        ));
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_handleOne = null;
        $this->_handleTwo = null;
        $this->_handleThree = null;
    }

    /**
     * Retrieve new callback, which when executed, records call identifier into call sequence and returns desired value
     *
     * @param ArrayObject $callSequence
     * @param string $callId
     * @param bool $returnValue
     * @return callable
     */
    protected function _createCallRecorder(ArrayObject $callSequence, $callId, $returnValue)
    {
        return function () use ($callSequence, $callId, $returnValue) {
            $callSequence[] = $callId;
            return $returnValue;
        };
    }

    /**
     * @param array $handles
     * @param string $expectedExceptionMsg
     *
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException(array $handles, $expectedExceptionMsg)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedExceptionMsg);
        new Magento_Performance_Scenario_Handler_Aggregate($handles);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'empty handles' => array(
                array(),
                'At least one scenario handler must be defined.',
            ),
            'invalid handle instance' => array(
                array(new stdClass()),
                'Scenario handler must implement "Magento_Performance_Scenario_HandlerInterface".',
            ),
        );
    }

    public function testRunDelegationSequence()
    {
        $scenarioFile = 'scenario.jmx';
        $scenarioParams = new Magento_Performance_Scenario_Arguments(array());
        $reportFile = 'scenario.jtl';
        $callSequence = new ArrayObject();
        $this->_handleOne
            ->expects($this->once())
            ->method('run')
            ->with($scenarioFile, $scenarioParams, $reportFile)
            ->will($this->returnCallback($this->_createCallRecorder($callSequence, 'handleOne', false)))
        ;
        $this->_handleTwo
            ->expects($this->once())
            ->method('run')
            ->with($scenarioFile, $scenarioParams, $reportFile)
            ->will($this->returnCallback($this->_createCallRecorder($callSequence, 'handleTwo', false)))
        ;
        $this->_handleThree
            ->expects($this->once())
            ->method('run')
            ->with($scenarioFile, $scenarioParams, $reportFile)
            ->will($this->returnCallback($this->_createCallRecorder($callSequence, 'handleThree', false)))
        ;
        $this->assertFalse($this->_object->run($scenarioFile, $scenarioParams, $reportFile));
        $this->assertEquals(array('handleOne', 'handleTwo', 'handleThree'), (array)$callSequence);
    }

    public function testRunStopOnSuccess()
    {
        $callSequence = new ArrayObject();
        $this->_handleOne
            ->expects($this->once())
            ->method('run')
            ->will($this->returnCallback($this->_createCallRecorder($callSequence, 'handleOne', false)))
        ;
        $this->_handleTwo
            ->expects($this->once())
            ->method('run')
            ->will($this->returnCallback($this->_createCallRecorder($callSequence, 'handleTwo', true)))
        ;
        $this->_handleThree
            ->expects($this->never())
            ->method('run')
        ;
        $this->assertTrue($this->_object->run('scenario.jmx', new Magento_Performance_Scenario_Arguments(array())));
        $this->assertEquals(array('handleOne', 'handleTwo'), (array)$callSequence);
    }
}
