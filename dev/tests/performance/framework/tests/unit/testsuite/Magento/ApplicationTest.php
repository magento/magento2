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

class Magento_ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Config
     */
    protected $_config;

    /**
     * @var Magento_Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var Magento_Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_installerScript;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * @var array
     */
    protected $_fixtureConfigData;

    /**
     * @var array
     */
    protected  $_appEvents = array();

    /**
     * @var array
     */
    protected  $_fixtureEvents = array();

    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . '/Performance/_files';
        $this->_fixtureConfigData = require($this->_fixtureDir . '/config_data.php');

        $this->_installerScript = realpath($this->_fixtureDir . '/app_base_dir//dev/shell/install.php');

        $this->_config = new Magento_Performance_Config(
            $this->_fixtureConfigData, $this->_fixtureDir, $this->_fixtureDir . '/app_base_dir'
        );
        $this->_shell = $this->getMock('Magento_Shell', array('execute'));

        $this->_object = $this->getMock(
            'Magento_Application',
            array('_bootstrap', '_cleanupMage', '_reindex', '_updateFilesystemPermissions'),
            array($this->_config, $this->_shell)
        );
        $this->_object->expects($this->any())
            ->method('_reindex')
            ->will($this->returnValue($this->_object));
    }

    protected function tearDown()
    {
        unset($this->_config);
        unset($this->_shell);
        unset($this->_object);
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testConstructorException()
    {
        $invalidAppDir = __DIR__;
        new Magento_Application(
            new Magento_Performance_Config($this->_fixtureConfigData, $this->_fixtureDir, $invalidAppDir),
            $this->_shell
        );
    }

    public function testInstall()
    {
        $this->_shell
            ->expects($this->at(1))
            ->method('execute')
            ->with(
                // @codingStandardsIgnoreStart
                'php -f %s -- --option1 %s --option2 %s --url %s --secure_base_url %s --admin_frontname %s --admin_username %s --admin_password %s',
                // @codingStandardsIgnoreEnd
                array(
                    $this->_installerScript,
                    'value 1',
                    'value 2',
                    'http://127.0.0.1/',
                    'http://127.0.0.1/',
                    'backend',
                    'admin',
                    'password1',
                )
            )
        ;

        $this->_object
            ->expects($this->once())
            ->method('_reindex')
        ;

        $this->_object
            ->expects($this->once())
            ->method('_updateFilesystemPermissions')
        ;

        $this->_object->install();
    }

    public function testApplyFixtures()
    {
        $application = $this->_buildApplicationForFixtures();

        $this->_testApplyFixtures(
            $application,
            array(),
            array(),
            array('uninstall', 'install', 'reindex', 'updateFilesystemPermissions'),
            'Testing initial install'
        );

        $this->_testApplyFixtures(
            $application,
            array('fixture1'),
            array('fixture1'),
            array('bootstrap', 'reindex', 'updateFilesystemPermissions'),
            'Testing first fixture'
        );

        $this->_testApplyFixtures(
            $application,
            array('fixture1'),
            array(),
            array(),
            'Testing same fixture'
        );

        $this->_testApplyFixtures(
            $application,
            array('fixture2', 'fixture1'),
            array('fixture2'),
            array('bootstrap', 'reindex', 'updateFilesystemPermissions'),
            'Testing superior fixture set'
        );

        $this->_testApplyFixtures(
            $application,
            array('fixture2', 'fixture3'),
            array('fixture2', 'fixture3'),
            array('uninstall', 'install', 'bootstrap', 'reindex', 'updateFilesystemPermissions'),
            'Testing incompatible fixture set'
        );
    }

    /**
     * Builds application mocked object, so it will produce tracked events, used for fixture application testing
     *
     * @return Magento_Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _buildApplicationForFixtures()
    {
        $test = $this;

        $funcShellEvent = function ($command, $arguments) use ($test) {
            $command = vsprintf($command, $arguments);
            if (strpos($command, 'uninstall') !== false) {
                $test->addAppEvent('uninstall');
            } else if (strpos($command, 'install') !== false) {
                $test->addAppEvent('install');
            }
        };
        $this->_shell->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback($funcShellEvent));

        $app = $this->getMock(
            'Magento_Application',
            array('_bootstrap', '_reindex', '_updateFilesystemPermissions', '_cleanupMage'),
            array($this->_config, $this->_shell)
        );

        // @codingStandardsIgnoreStart
        $app->expects($this->any())
            ->method('_bootstrap')
            ->will($this->returnCallback(function () use ($test, $app) {
                $test->addAppEvent('bootstrap');
                return $app;
            })
        );

        $app->expects($this->any())
            ->method('_reindex')
            ->will($this->returnCallback(function () use ($test, $app) {
                $test->addAppEvent('reindex');
                return $app;
            })
        );

        $app->expects($this->any())
            ->method('_updateFilesystemPermissions')
            ->will($this->returnCallback(function () use ($test, $app) {
                $test->addAppEvent('updateFilesystemPermissions');
                return $app;
            })
        );
        // @codingStandardsIgnoreEnd

        return $app;
    }

    /**
     * Test application of fixtures, asserting that proper fixtures have been applied,
     * and application events have happened
     *
     * @param Magento_Application|PHPUnit_Framework_MockObject_MockObject $application
     * @param array $fixtures
     * @param array $expectedFixtures
     * @param array $expectedEvents
     * @param string $message
     */
    protected function _testApplyFixtures($application, $fixtures, $expectedFixtures, $expectedEvents, $message)
    {
        // Prepare
        $nameToPathFunc = function ($fixture) {
            return __DIR__ . "/_files/application_test/{$fixture}.php";
        };
        $fixtures = array_map($nameToPathFunc, $fixtures);

        $this->_appEvents = array();
        $this->_fixtureEvents = array();

        // Run
        $GLOBALS['applicationTestForFixtures'] = $this; // Expose itself to fixtures
        try {
            $application->applyFixtures($fixtures);

            // Assert expectations
            $fixtureEvents = array_keys($this->_fixtureEvents);
            sort($fixtureEvents);
            sort($expectedFixtures);
            $this->assertEquals($expectedFixtures, $fixtureEvents, "$message - fixtures applied are wrong");

            $appEvents = array_keys($this->_appEvents);
            sort($appEvents);
            sort($expectedEvents);
            $this->assertEquals($expectedEvents, $appEvents, "$message - application management is wrong");

            unset($GLOBALS['applicationTestForFixtures']);
        } catch (Exception $e) {
            unset($GLOBALS['applicationTestForFixtures']);
            throw $e;
        }
    }

    /**
     * Log event that happened in application object
     *
     * @param string $name
     */
    public function addAppEvent($name)
    {
        $this->_appEvents[$name] = true;
    }

    /**
     * Log event that happened in fixtures
     *
     * @param string $name
     */
    public function addFixtureEvent($name)
    {
        $this->_fixtureEvents[$name] = true;
    }
}
