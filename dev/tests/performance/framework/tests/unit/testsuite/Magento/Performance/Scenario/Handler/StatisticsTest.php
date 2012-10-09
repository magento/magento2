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

class Magento_Performance_Scenario_Handler_StatisticsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Scenario_Handler_Statistics
     */
    protected $_object;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handler;

    /**
     * @var Magento_Performance_Scenario_Arguments
     */
    protected $_scenarioArgs;

    protected function setUp()
    {
        $this->_handler = $this->getMockForAbstractClass('Magento_Performance_Scenario_HandlerInterface');
        $this->_object = new Magento_Performance_Scenario_Handler_Statistics($this->_handler);
        $this->_scenarioArgs = new Magento_Performance_Scenario_Arguments(array());
    }

    protected function tearDown()
    {
        $this->_handler = null;
        $this->_object = null;
        $this->_scenarioArgs = null;
    }

    public function testRunDelegation()
    {
        $scenarioFile = 'scenario.jmx';
        $reportFile = 'scenario.jtl';
        $expectedResult = new stdClass();
        $this->_handler
            ->expects($this->once())
            ->method('run')
            ->with($scenarioFile, $this->_scenarioArgs, $reportFile)
            ->will($this->returnValue($expectedResult))
        ;
        $this->assertSame($expectedResult, $this->_object->run($scenarioFile, $this->_scenarioArgs, $reportFile));
    }

    public function testRunFailure()
    {
        $scenarioFile = 'scenario.jmx';
        $reportFile = 'scenario.jtl';
        $failure = new Magento_Performance_Scenario_FailureException($scenarioFile);
        $this->_handler
            ->expects($this->once())
            ->method('run')
            ->with($scenarioFile, $this->_scenarioArgs, $reportFile)
            ->will($this->throwException($failure))
        ;
        $this->assertTrue($this->_object->run($scenarioFile, $this->_scenarioArgs, $reportFile));
        return array('scenario' => $scenarioFile, 'object' => $this->_object, 'actualFailure' => $failure);
    }

    /**
     * @depends testRunFailure
     *
     * @param array $arguments
     */
    public function testGetFailures(array $arguments)
    {
        $scenario = $arguments['scenario'];
        /** @var $object Magento_Performance_Scenario_Handler_Statistics */
        $object = $arguments['object'];
        /** @var $failure Magento_Performance_Scenario_FailureException */
        $failure = $arguments['actualFailure'];
        $this->assertSame(array($scenario => $failure), $object->getFailures());
    }

    /**
     * @dataProvider onScenarioFirstRunDataProvider
     *
     * @param PHPUnit_Framework_MockObject_Stub $handlerReturnStub
     */
    public function testOnScenarioFirstRun(PHPUnit_Framework_MockObject_Stub $handlerReturnStub)
    {
        $this->_handler
            ->expects($this->any())
            ->method('run')
            ->will($handlerReturnStub)
        ;
        $notifications = array();
        $this->_object->onScenarioFirstRun(function ($scenarioFile) use (&$notifications) {
            $notifications[] = $scenarioFile;
        });

        $this->_object->run('scenario_one.jmx', $this->_scenarioArgs);
        $this->assertEquals(array('scenario_one.jmx'), $notifications);

        // scenario has been already processed, no notification should occur
        $this->_object->run('scenario_one.jmx', $this->_scenarioArgs);
        $this->assertEquals(array('scenario_one.jmx'), $notifications);

        // new scenario, notification should happen
        $this->_object->run('scenario_two.jmx', $this->_scenarioArgs);
        $this->assertEquals(array('scenario_one.jmx', 'scenario_two.jmx'), $notifications);

        // both scenarios have been already processed, nothing should be done
        $this->_object->run('scenario_one.jmx', $this->_scenarioArgs);
        $this->_object->run('scenario_two.jmx', $this->_scenarioArgs);
        $this->assertEquals(array('scenario_one.jmx', 'scenario_two.jmx'), $notifications);
    }

    public function onScenarioFirstRunDataProvider()
    {
        return array(
            'success' => array($this->returnValue(true)),
            'failure' => array($this->throwException(new Magento_Performance_Scenario_FailureException)),
        );
    }

    public function testOnScenarioFailure()
    {
        $failure = new Magento_Performance_Scenario_FailureException;
        $this->_handler
            ->expects($this->any())
            ->method('run')
            ->will($this->throwException($failure))
        ;
        $notifications = array();
        $this->_object->onScenarioFailure(function ($scenarioFile, $actualFailure) use (&$notifications, $failure) {
            PHPUnit_Framework_Assert::assertSame($failure, $actualFailure);
            $notifications[] = $scenarioFile;
        });

        $this->_object->run('scenario.jmx', $this->_scenarioArgs);
        $this->assertEquals(array('scenario.jmx'), $notifications);

        $this->_object->run('scenario.jmx', $this->_scenarioArgs);
        $this->assertEquals(array('scenario.jmx', 'scenario.jmx'), $notifications);
    }
}
