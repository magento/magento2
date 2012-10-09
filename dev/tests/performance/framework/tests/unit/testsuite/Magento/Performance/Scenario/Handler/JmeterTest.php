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

class Magento_Performance_Scenario_Handler_JmeterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var Magento_Performance_Scenario_Handler_Jmeter|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_scenarioFile;

    /**
     * @var string
     */
    protected $_reportFile;

    /**
     * @var Magento_Performance_Scenario_Arguments
     */
    protected $_scenarioArgs;

    protected function setUp()
    {
        $this->_scenarioFile = realpath(__DIR__ . '/../../_files/scenario.jmx');
        $this->_reportFile = realpath(__DIR__ . '/../../_files') . DIRECTORY_SEPARATOR . 'scenario.jtl';
        $this->_scenarioArgs = new Magento_Performance_Scenario_Arguments(array(
            Magento_Performance_Scenario_Arguments::ARG_HOST  => '127.0.0.1',
            Magento_Performance_Scenario_Arguments::ARG_PATH  => '/',
            Magento_Performance_Scenario_Arguments::ARG_USERS => 2,
        ));
        $this->_shell = $this->getMock('Magento_Shell', array('execute'));
        $this->_object = new Magento_Performance_Scenario_Handler_Jmeter($this->_shell);
    }

    protected function tearDown()
    {
        $this->_shell = null;
        $this->_object = null;
        $this->_scenarioArgs = null;
    }

    public function testConstructor()
    {
        $this->_shell
            ->expects($this->once())
            ->method('execute')
            ->with('jmeter --version')
        ;
        $this->_object->__construct($this->_shell);
    }

    public function testRunUnsupportedScenarioFormat()
    {
        $this->_shell
            ->expects($this->never())
            ->method('execute')
        ;
        $this->assertFalse($this->_object->run('scenario.txt', $this->_scenarioArgs));
    }

    public function testRunNoReport()
    {
        $this->_shell
            ->expects($this->once())
            ->method('execute')
            ->with(
                'jmeter -n -t %s %s %s %s %s',
                array($this->_scenarioFile, '-Jhost=127.0.0.1', '-Jpath=/', '-Jusers=2', '-Jloops=1')
            )
        ;
        $this->assertTrue($this->_object->run($this->_scenarioFile, $this->_scenarioArgs));
    }

    public function testRunReport()
    {
        $this->_shell
            ->expects($this->once())
            ->method('execute')
            ->with(
                'jmeter -n -t %s -l %s %s %s %s %s',
                array(
                    $this->_scenarioFile, $this->_reportFile, '-Jhost=127.0.0.1', '-Jpath=/', '-Jusers=2', '-Jloops=1'
                )
            )
        ;
        $this->assertTrue($this->_object->run($this->_scenarioFile, $this->_scenarioArgs, $this->_reportFile));
    }

    /**
     * @param string $scenarioFile
     * @param string $reportFile
     * @param string $expectedException
     * @param string $expectedExceptionMsg
     * @dataProvider runExceptionDataProvider
     */
    public function testRunException($scenarioFile, $reportFile, $expectedException, $expectedExceptionMsg = '')
    {
        $this->setExpectedException($expectedException, $expectedExceptionMsg);
        $this->_object->run($scenarioFile, $this->_scenarioArgs, $reportFile);
    }

    public function runExceptionDataProvider()
    {
        $fixtureDir = realpath(__DIR__ . '/../../_files');
        return array(
            'no report created' => array(
                "$fixtureDir/scenario_without_report.jmx",
                "$fixtureDir/scenario_without_report.jtl",
                'Magento_Exception',
                "Report file '$fixtureDir/scenario_without_report.jtl' has not been created.",
            ),
            'scenario failure in report' => array(
                "$fixtureDir/scenario_failure.jmx",
                "$fixtureDir/scenario_failure.jtl",
                'Magento_Performance_Scenario_FailureException',
                'fixture failure message',
            ),
            'scenario error in report' => array(
                "$fixtureDir/scenario_error.jmx",
                "$fixtureDir/scenario_error.jtl",
                'Magento_Performance_Scenario_FailureException',
                'fixture error message',
            ),
        );
    }
}
