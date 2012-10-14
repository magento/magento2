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

class Magento_Performance_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Config
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * @var string
     */
    protected $_appBaseDir;

    /**
     * @var array
     */
    protected $_fixtureConfigData;

    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        $this->_appBaseDir = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'app_base_dir';
        $this->_fixtureConfigData = require $this->_fixtureDir . '/config_data.php';
        $this->_object = new Magento_Performance_Config(
            $this->_fixtureConfigData, $this->_fixtureDir, $this->_appBaseDir
        );
    }

    protected function tearDown()
    {
        unset($this->_object);
    }

    /**
     * @dataProvider constructorExceptionDataProvider
     * @param array $configData
     * @param string $baseDir
     * @param string $expectedException
     * @param string $expectedExceptionMsg
     */
    public function testConstructorException(array $configData, $baseDir, $expectedException, $expectedExceptionMsg)
    {
        $this->setExpectedException($expectedException, $expectedExceptionMsg);
        new Magento_Performance_Config($configData, $baseDir, $this->_appBaseDir);
    }

    /**
     * @return array
     */
    public function constructorExceptionDataProvider()
    {
        return array(
            'non-existing base dir' => array(
                require __DIR__ . '/_files/config_data.php',
                'non_existing_dir',
                'Magento_Exception',
                "Base directory 'non_existing_dir' does not exist",
            ),
            'invalid fixtures format' => array(
                require __DIR__ . '/_files/config_data_invalid_fixtures_format.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "Scenario 'fixtures' option must be an array, not a value: 'string_fixtures_*.php'",
            ),
            'non-existing fixture' => array(
                require __DIR__ . '/_files/config_data_non_existing_fixture.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'Magento_Exception',
                "Fixture 'non_existing_fixture.php' doesn't exist",
            ),
            'invalid scenarios format' => array(
                require __DIR__ . '/_files/config_data_invalid_scenarios_format.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "'scenario' => 'scenarios' option must be an array",
            ),
            'non-existing scenario' => array(
                require __DIR__ . '/_files/config_data_non_existing_scenario.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'Magento_Exception',
                "Scenario 'non_existing_scenario.jmx' doesn't exist",
            ),
        );
    }

    public function testGetApplicationBaseDir()
    {
        $this->assertEquals($this->_appBaseDir, $this->_object->getApplicationBaseDir());
    }

    public function testGetApplicationUrlHost()
    {
        $this->assertEquals('127.0.0.1', $this->_object->getApplicationUrlHost());
    }

    public function testGetApplicationUrlPath()
    {
        $this->assertEquals('/', $this->_object->getApplicationUrlPath());
    }

    public function testGetAdminOptions()
    {
        $expectedOptions = array(
            'frontname' => 'backend',
            'username' => 'admin',
            'password' => 'password1',
        );
        $this->assertEquals($expectedOptions, $this->_object->getAdminOptions());
    }

    public function testGetInstallOptions()
    {
        $expectedOptions = array('option1' => 'value 1', 'option2' => 'value 2');
        $this->assertEquals($expectedOptions, $this->_object->getInstallOptions());
    }

    public function testGetScenarios()
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $expectedScenarios = array(
            $dir . 'scenario.jmx',
            $dir . 'scenario_error.jmx',
            $dir . 'scenario_failure.jmx',
        );
        $actualScenarios = $this->_object->getScenarios();
        sort($actualScenarios);
        $this->assertEquals($expectedScenarios, $actualScenarios);
    }

    /**
     * @dataProvider getScenarioArgumentsDataProvider
     *
     * @param string $scenarioName
     * @param array $expectedArgs
     */
    public function testGetScenarioArguments($scenarioName, array $expectedArgs)
    {
        $scenarioFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $scenarioName;
        $actualResult = $this->_object->getScenarioArguments($scenarioFile);
        $this->assertInstanceOf('Magento_Performance_Scenario_Arguments', $actualResult);
        $this->assertEquals($expectedArgs, (array)$actualResult);
    }

    public function getScenarioArgumentsDataProvider()
    {
        $fixtureParams = array(
            Magento_Performance_Scenario_Arguments::ARG_USERS             => 1,
            Magento_Performance_Scenario_Arguments::ARG_LOOPS             => 1,
            Magento_Performance_Scenario_Arguments::ARG_HOST              => '127.0.0.1',
            Magento_Performance_Scenario_Arguments::ARG_PATH              => '/',
            Magento_Performance_Scenario_Arguments::ARG_ADMIN_FRONTNAME   => 'backend',
            Magento_Performance_Scenario_Arguments::ARG_ADMIN_USERNAME    => 'admin',
            Magento_Performance_Scenario_Arguments::ARG_ADMIN_PASSWORD    => 'password1',
            'arg1'                                                        => 'value 1',
            'arg2'                                                        => 'value 2',
        );
        return array(
            'common arguments inheritance' => array(
                'scenario_error.jmx',
                $fixtureParams,
            ),
            'scenario-specific arguments' => array(
                'scenario.jmx',
                array_merge($fixtureParams, array('arg2' => 'overridden value 2', 'arg3' => 'custom value 3')),
            ),
            'no overriding crosscutting argument' => array(
                'scenario_failure.jmx',
                $fixtureParams,
            ),
        );
    }

    /**
     * @dataProvider getScenarioFixturesDataProvider
     *
     * @param string $scenarioName
     * @param array $expectedFixtures
     */
    public function testGetScenarioFixtures($scenarioName, array $expectedFixtures)
    {
        $scenarioFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $scenarioName;
        $actualResult = $this->_object->getScenarioFixtures($scenarioFile);
        $this->assertEquals($expectedFixtures, $actualResult);
    }

    public function getScenarioFixturesDataProvider()
    {
        return array(
            'normal fixtures' => array(
                'scenario.jmx',
                array(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'fixture.php'),
            ),
            'direct scenario declaration' => array(
                'scenario_error.jmx',
                array(),
            ),
            'scenario without fixtures' => array(
                'scenario_failure.jmx',
                array(),
            ),
        );
    }

    /**
     * @dataProvider getScenarioSettingsDataProvider
     *
     * @param string $scenarioName
     * @param array $expectedResult
     */
    public function testGetScenarioSettings($scenarioName, array $expectedResult)
    {
        $scenarioFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $scenarioName;
        $actualResult = $this->_object->getScenarioSettings($scenarioFile);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function getScenarioSettingsDataProvider()
    {
        return array(
            'common settings inheritance' => array(
                'scenario_error.jmx',
                array('setting1' => 'setting 1', 'setting2' => 'setting 2')
            ),
            'scenario-specific settings' => array(
                'scenario.jmx',
                array('setting1' => 'setting 1', 'setting2' => 'overridden setting 2', 'setting3' => 'setting 3')
            ),
        );
    }

    public function testGetScenarioArgumentsNonExistingScenario()
    {
        $this->assertNull($this->_object->getScenarioArguments('non_existing.jmx'));
    }

    public function testGetReportDir()
    {
        $expectedReportDir = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'report';
        $this->assertEquals($expectedReportDir, $this->_object->getReportDir());
    }
}
