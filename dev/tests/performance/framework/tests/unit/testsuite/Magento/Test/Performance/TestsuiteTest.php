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
namespace Magento\Test\Performance;

/**
 * Class TestsuiteTest
 *
 */
class TestsuiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testsuite object
     *
     * @var \Magento\TestFramework\Performance\Testsuite
     */
    protected $_object;

    /**
     * Config object
     *
     * @var \Magento\TestFramework\Performance\Config
     */
    protected $_config;

    /**
     * Application object
     *
     * @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_application;

    /**
     * Scenario handler
     *
     * @var \Magento\TestFramework\Performance\Scenario\HandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handler;

    /**
     * Fixtures directory
     *
     * @var string
     */
    protected $_fixtureDir;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . '/_files';
        $fixtureConfigData = include $this->_fixtureDir . '/config_data.php';

        $shell = $this->getMock('Magento\Framework\Shell', array('execute'), array(), '', false);
        $this->_config = new \Magento\TestFramework\Performance\Config(
            $fixtureConfigData,
            $this->_fixtureDir,
            $this->_fixtureDir . '/app_base_dir'
        );
        $this->_application = $this->getMock(
            'Magento\TestFramework\Application',
            array('applyFixtures'),
            array($this->_config, $this->getMockForAbstractClass('Magento\Framework\ObjectManager'), $shell)
        );
        $this->_handler = $this->getMockForAbstractClass(
            'Magento\TestFramework\Performance\Scenario\HandlerInterface'
        );
        $this->_object = new \Magento\TestFramework\Performance\Testsuite(
            $this->_config,
            $this->_application,
            $this->_handler
        );
    }

    /**
     * Teardown after test
     */
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
     * @param string $scenarioTitle
     * @param string $scenarioFile
     * @param integer $invocationIndex
     * @param \PHPUnit_Framework_MockObject_Stub $returnStub
     */
    protected function _expectScenarioWarmUp(
        $scenarioTitle,
        $scenarioFile,
        $invocationIndex,
        \PHPUnit_Framework_MockObject_Stub $returnStub = null
    ) {
        $scenarioFilePath = $this->_fixtureDir . '/' . $scenarioFile;

        /** @var $invocationMocker \PHPUnit_Framework_MockObject_Builder_InvocationMocker */
        $invocationMocker = $this->_handler->expects($this->at($invocationIndex));
        $invocationMocker->method(
            'run'
        )->with(
            $this->logicalAnd(
                $this->isInstanceOf('Magento\TestFramework\Performance\Scenario'),
                $this->objectHasAttribute('_title', $scenarioTitle),
                $this->objectHasAttribute('_file', $scenarioFilePath)
            ),
            $this->isNull()
        );
        if ($returnStub) {
            $invocationMocker->will($returnStub);
        }
    }

    /**
     * Setup expectation of a scenario invocation with report generation
     *
     * @param string $scenarioTitle
     * @param string $scenarioFile
     * @param integer $invocationIndex
     * @param \PHPUnit_Framework_MockObject_Stub $returnStub
     */
    protected function _expectScenarioRun(
        $scenarioTitle,
        $scenarioFile,
        $invocationIndex,
        \PHPUnit_Framework_MockObject_Stub $returnStub = null
    ) {
        $scenarioFilePath = $this->_fixtureDir . '/' . $scenarioFile;
        $reportFile = basename($scenarioFile, '.jmx') . '.jtl';

        /** @var $invocationMocker \PHPUnit_Framework_MockObject_Builder_InvocationMocker */
        $invocationMocker = $this->_handler->expects($this->at($invocationIndex));
        $invocationMocker->method(
            'run'
        )->with(
            $this->logicalAnd(
                $this->isInstanceOf('Magento\TestFramework\Performance\Scenario'),
                $this->objectHasAttribute('_title', $scenarioTitle),
                $this->objectHasAttribute('_file', $scenarioFilePath)
            ),
            $this->_fixtureDir . '/report/' . $reportFile
        );
        if ($returnStub) {
            $invocationMocker->will($returnStub);
        }
    }

    /**
     * Test run testsuite
     */
    public function testRun()
    {
        $this->_expectScenarioWarmUp('Scenario with Error', 'scenario_error.jmx', 0);
        $this->_expectScenarioRun('Scenario with Error', 'scenario_error.jmx', 1);

        /* Warm up is disabled for scenario */
        $this->_expectScenarioRun('Scenario with Failure', 'scenario_failure.jmx', 2);

        $this->_expectScenarioWarmUp('Scenario', 'scenario.jmx', 3);
        $this->_expectScenarioRun('Scenario', 'scenario.jmx', 4);

        $this->_object->run();
    }

    /**
     * Scenario run test
     */
    public function testOnScenarioRun()
    {
        $this->_handler->expects($this->any())->method('run');
        $notifications = array();
        $this->_object->onScenarioRun(
            function ($scenario) use (&$notifications) {
                $notifications[] = $scenario->getFile();
            }
        );
        $this->_object->run();
        $this->assertEquals(
            array(
                realpath($this->_fixtureDir . '/scenario_error.jmx'),
                realpath($this->_fixtureDir . '/scenario_failure.jmx'),
                realpath($this->_fixtureDir . '/scenario.jmx')
            ),
            $notifications
        );
    }

    /**
     * Test exception on scenario run
     *
     * @expectedException \BadFunctionCallException
     */
    public function testOnScenarioRunException()
    {
        $this->_object->onScenarioRun('invalid_callback');
    }

    /**
     * Test scenario failure
     */
    public function testOnScenarioFailure()
    {
        $scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario with Error',
            'scenario_error.jmx',
            array(),
            array(),
            array()
        );
        $scenarioOneFailure = $this->throwException(
            new \Magento\TestFramework\Performance\Scenario\FailureException($scenario)
        );
        $this->_expectScenarioWarmUp('Scenario with Error', 'scenario_error.jmx', 0, $scenarioOneFailure);
        $this->_expectScenarioRun('Scenario with Error', 'scenario_error.jmx', 1, $scenarioOneFailure);

        /* Warm up is disabled for scenario */
        $scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario with Failure',
            'scenario_failure.jmx',
            array(),
            array(),
            array()
        );
        $scenarioTwoFailure = $this->throwException(
            new \Magento\TestFramework\Performance\Scenario\FailureException($scenario)
        );
        $this->_expectScenarioRun('Scenario with Failure', 'scenario_failure.jmx', 2, $scenarioTwoFailure);

        $scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            'scenario.jmx',
            array(),
            array(),
            array()
        );
        $scenarioThreeFailure = $this->throwException(
            new \Magento\TestFramework\Performance\Scenario\FailureException($scenario)
        );
        $this->_expectScenarioWarmUp('Scenario', 'scenario.jmx', 3);
        $this->_expectScenarioRun('Scenario', 'scenario.jmx', 4, $scenarioThreeFailure);

        $notifications = array();
        $this->_object->onScenarioFailure(
            function (
                \Magento\TestFramework\Performance\Scenario\FailureException $actualFailure
            ) use (
                &$notifications
            ) {
                $notifications[] = $actualFailure->getScenario()->getFile();
            }
        );
        $this->_object->run();
        $this->assertEquals(array('scenario_error.jmx', 'scenario_failure.jmx', 'scenario.jmx'), $notifications);
    }

    /**
     * Test exception on scenario failure
     *
     * @expectedException \BadFunctionCallException
     */
    public function testOnScenarioFailureException()
    {
        $this->_object->onScenarioFailure(array($this, 'invalid_callback'));
    }
}
