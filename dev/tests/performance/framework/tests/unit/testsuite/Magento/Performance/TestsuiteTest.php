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

class Magento_Performance_TestsuiteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Testsuite
     */
    protected $_object;

    /**
     * @var Magento_Performance_Config
     */
    protected $_config;

    /**
     * @var Magento_Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_application;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handler;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * @var string
     */
    protected $_appBaseDir;

    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        $fixtureConfigData = include($this->_fixtureDir . DIRECTORY_SEPARATOR . 'config_data.php');
        $shell = $this->getMock('Magento_Shell', array('execute'));
        $this->_config = new Magento_Performance_Config(
            $fixtureConfigData,
            $this->_fixtureDir,
            $this->_fixtureDir . '/app_base_dir'
        );
        $this->_application = $this->getMock(
            'Magento_Application', array('applyFixtures'), array($this->_config, $shell)
        );
        $this->_handler = $this->getMockForAbstractClass('Magento_Performance_Scenario_HandlerInterface');
        $this->_object = new Magento_Performance_Testsuite($this->_config, $this->_application, $this->_handler);
    }

    protected function tearDown()
    {
        $this->_config = null;
        $this->_application = null;
        $this->_handler = null;
        $this->_object = null;
    }

    /**
     * Setup expectation of a scenario warm up invocation
     *
     * @param string $scenarioName
     * @param integer $invocationIndex
     */
    protected function _expectScenarioWarmUp($scenarioName, $invocationIndex)
    {
        $this->_handler
            ->expects($this->at($invocationIndex))
            ->method('run')
            ->with(
                $this->_fixtureDir . DIRECTORY_SEPARATOR . $scenarioName . '.jmx',
                $this->isInstanceOf('Magento_Performance_Scenario_Arguments'),
                $this->isNull()
            )
            ->will($this->returnValue(true))
        ;
    }

    /**
     * Setup expectation of a scenario invocation with report generation
     *
     * @param string $scenarioName
     * @param integer $invocationIndex
     */
    protected function _expectScenarioRun($scenarioName, $invocationIndex)
    {
        $this->_handler
            ->expects($this->at($invocationIndex))
            ->method('run')
            ->with(
                $this->_fixtureDir . DIRECTORY_SEPARATOR . $scenarioName . '.jmx',
                $this->isInstanceOf('Magento_Performance_Scenario_Arguments'),
                $this->_fixtureDir . DIRECTORY_SEPARATOR . 'report' . DIRECTORY_SEPARATOR . $scenarioName . '.jtl'
            )
            ->will($this->returnValue(true))
        ;
    }

    public function testRun()
    {
        $this->_expectScenarioWarmUp('scenario_error', 0);
        $this->_expectScenarioRun('scenario_error', 1);

        /* Warm up is disabled for scenario */
        $this->_expectScenarioRun('scenario_failure', 2);

        $this->_expectScenarioWarmUp('scenario', 3);
        $this->_expectScenarioRun('scenario', 4);

        $this->_object->run();
    }

    public function testRunException()
    {
        $expectedScenario = $this->_fixtureDir . DIRECTORY_SEPARATOR . 'scenario_error.jmx';
        $this->setExpectedException(
            'Magento_Exception', "Unable to run scenario '$expectedScenario', format is not supported."
        );
        $this->_handler
            ->expects($this->atLeastOnce())
            ->method('run')
            ->will($this->returnValue(false))
        ;
        $this->_object->run();
    }
}
