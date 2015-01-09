<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Performance;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Performance\Config
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * @var array
     */
    protected $_fixtureConfigData;

    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . '/_files';
        $this->_fixtureConfigData = require $this->_fixtureDir . '/config_data.php';
        $this->_object = new \Magento\TestFramework\Performance\Config(
            $this->_fixtureConfigData,
            $this->_fixtureDir,
            $this->_getFixtureAppBaseDir()
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
        new \Magento\TestFramework\Performance\Config($configData, $baseDir, $this->_getFixtureAppBaseDir());
    }

    /**
     * Get simulated application base directory
     *
     * @return string
     */
    protected function _getFixtureAppBaseDir()
    {
        return __DIR__ . '/_files/app_base_dir';
    }

    /**
     * @return array
     */
    public function constructorExceptionDataProvider()
    {
        return [
            'non-existing base dir' => [
                require __DIR__ . '/_files/config_data.php',
                'non_existing_dir',
                'Magento\Framework\Exception',
                "Base directory 'non_existing_dir' does not exist",
            ],
            'invalid scenarios format' => [
                require __DIR__ . '/_files/config_data_invalid_scenarios_format.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                "'scenario' => 'scenarios' option must be an array",
            ],
            'no scenario title' => [
                require __DIR__ . '/_files/config_no_title.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                'Scenario must have a title',
            ],
            'bad users scenario argument' => [
                require __DIR__ . '/_files/config_bad_users.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                "Scenario 'Scenario' must have a positive integer argument 'users'.",
            ],
            'bad loops scenario argument' => [
                require __DIR__ . '/_files/config_bad_loops.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                "Scenario 'Scenario' must have a positive integer argument 'loops'.",
            ],
            'invalid scenario fixtures format' => [
                require __DIR__ . '/_files/config_invalid_fixtures_format.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                "'fixtures' for scenario 'Scenario' must be represented by an array",
            ],
            'no scenario file defined' => [
                require __DIR__ . '/_files/config_no_file_defined.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                "File is not defined for scenario 'Scenario'",
            ],
            'non-existing scenario file' => [
                require __DIR__ . '/_files/config_non_existing_file.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                "File non_existing_file.jmx doesn't exist for scenario 'Scenario'",
            ],
            'non-existing scenario fixture' => [
                require __DIR__ . '/_files/config_non_existing_fixture.php',
                __DIR__ . '/_files',
                'InvalidArgumentException',
                "Fixture 'non_existing_fixture.php' doesn't exist",
            ]
        ];
    }

    public function testGetApplicationBaseDir()
    {
        $this->assertEquals($this->_getFixtureAppBaseDir(), $this->_object->getApplicationBaseDir());
    }

    public function testGetApplicationUrlHost()
    {
        $this->assertEquals('127.0.0.1', $this->_object->getApplicationUrlHost());
    }

    public function testGetApplicationUrlPath()
    {
        $this->assertEquals('/', $this->_object->getApplicationUrlPath());
    }

    public function testGetInstallOptions()
    {
        $expectedOptions = [
            'option1' => 'value 1',
            'option2' => 'value 2',
            'backend_frontname' => 'backend',
            'admin_username' => 'admin',
            'admin_password' => 'password1',
        ];
        $this->assertEquals($expectedOptions, $this->_object->getInstallOptions());
    }

    public function testGetScenarios()
    {
        $actualScenarios = $this->_object->getScenarios();

        // Assert array of scenarios is correctly composed
        $this->assertInternalType('array', $actualScenarios);
        $this->assertCount(3, $actualScenarios);

        // Assert that the data is passed to scenarios successfully
        /** @var $scenario \Magento\TestFramework\Performance\Scenario */
        $scenario = $actualScenarios[0];
        $this->assertInstanceOf('Magento\TestFramework\Performance\Scenario', $scenario);

        $this->assertEquals('Scenario', $scenario->getTitle());
        $this->assertEquals(realpath(__DIR__ . '/_files/scenario.jmx'), $scenario->getFile());

        // Assert that default config is applied
        $expectedArguments = [
            \Magento\TestFramework\Performance\Scenario::ARG_USERS => 1,
            \Magento\TestFramework\Performance\Scenario::ARG_LOOPS => 1,
            \Magento\TestFramework\Performance\Scenario::ARG_HOST => '127.0.0.1',
            \Magento\TestFramework\Performance\Scenario::ARG_PATH => '/',
            \Magento\TestFramework\Performance\Scenario::ARG_BACKEND_FRONTNAME => 'backend',
            \Magento\TestFramework\Performance\Scenario::ARG_ADMIN_USERNAME => 'admin',
            \Magento\TestFramework\Performance\Scenario::ARG_ADMIN_PASSWORD => 'password1',
            \Magento\TestFramework\Performance\Scenario::ARG_BASEDIR => $this->_getFixtureAppBaseDir(),
            'arg1' => 'value 1',
            'arg2' => 'overridden value 2',
            'arg3' => 'custom value 3',
            'jmeter.save.saveservice.output_format' => 'xml',
        ];
        $this->assertEquals($expectedArguments, $scenario->getArguments());

        $expectedSettings = [
            'setting1' => 'setting 1',
            'setting2' => 'overridden setting 2',
            'setting3' => 'setting 3',
        ];
        $this->assertEquals($expectedSettings, $scenario->getSettings());

        $expectedSettings = [
            'setting1' => 'setting 1',
            'setting2' => 'overridden setting 2',
            'setting3' => 'setting 3',
        ];
        $this->assertEquals($expectedSettings, $scenario->getSettings());

        $expectedFixtures = [
            realpath(__DIR__ . '/_files/fixture.php'),
            realpath(__DIR__ . '/_files/fixture2.php'),
        ];
        $this->assertEquals($expectedFixtures, $scenario->getFixtures());
    }

    public function testGetReportDir()
    {
        $expectedReportDir = __DIR__ . '/_files/report';
        $this->assertEquals($expectedReportDir, $this->_object->getReportDir());
    }
}
