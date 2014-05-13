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

/**
 * Test class for \Magento\TestFramework\Bootstrap.
 */
namespace Magento\Test;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * Setting values required to be specified
     *
     * @var array
     */
    protected $_requiredSettings = array(
        'TESTS_LOCAL_CONFIG_FILE' => 'etc/local-mysql.xml',
        'TESTS_LOCAL_CONFIG_EXTRA_FILE' => 'etc/integration-tests-config.xml'
    );

    /**
     * @var \Magento\TestFramework\Bootstrap\Settings
     */
    protected $_settings;

    /**
     * @var \Magento\TestFramework\Bootstrap\Environment|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_envBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\DocBlock|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_docBlockBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\Profiler|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_profilerBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\Memory
     */
    protected $_memoryBootstrap;

    /**
     * @var \Magento\Framework\Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var string
     */
    protected $_integrationTestsDir;

    protected function setUp()
    {
        $this->_integrationTestsDir = realpath(__DIR__ . '/../../../../../../');
        $this->_settings = new \Magento\TestFramework\Bootstrap\Settings(
            $this->_integrationTestsDir,
            $this->_requiredSettings
        );
        $this->_envBootstrap = $this->getMock(
            'Magento\TestFramework\Bootstrap\Environment',
            array('emulateHttpRequest', 'emulateSession')
        );
        $this->_docBlockBootstrap = $this->getMock(
            'Magento\TestFramework\Bootstrap\DocBlock',
            array('registerAnnotations'),
            array(__DIR__)
        );
        $profilerDriver = $this->getMock('Magento\Framework\Profiler\Driver\Standard', array('registerOutput'));
        $this->_profilerBootstrap = $this->getMock(
            'Magento\TestFramework\Bootstrap\Profiler',
            array('registerFileProfiler', 'registerBambooProfiler'),
            array($profilerDriver)
        );
        $this->_memoryBootstrap = $this->getMock(
            'Magento\TestFramework\Bootstrap\Memory',
            array('activateStatsDisplaying', 'activateLimitValidation'),
            array(),
            '',
            false
        );
        $this->_shell = $this->getMock('Magento\Framework\Shell', array('execute'), array(), '', false);
        $this->_object = new \Magento\TestFramework\Bootstrap(
            $this->_settings,
            $this->_envBootstrap,
            $this->_docBlockBootstrap,
            $this->_profilerBootstrap,
            $this->_shell,
            __DIR__
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_settings = null;
        $this->_envBootstrap = null;
        $this->_docBlockBootstrap = null;
        $this->_profilerBootstrap = null;
        $this->_memoryBootstrap = null;
        $this->_shell = null;
    }

    /**
     * @param array $fixtureSettings
     * @return \Magento\TestFramework\Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _injectApplicationMock(array $fixtureSettings = array())
    {
        $fixtureSettings += $this->_requiredSettings;
        $application = $this->getMock(
            'Magento\TestFramework\Application',
            array('cleanup', 'isInstalled', 'initialize', 'install'),
            array(),
            '',
            false
        );
        $settings = new \Magento\TestFramework\Bootstrap\Settings($this->_integrationTestsDir, $fixtureSettings);
        // prevent calling the constructor because of mocking the method it invokes
        $this->_object = $this->getMock(
            'Magento\TestFramework\Bootstrap',
            array('_createApplication', '_createMemoryBootstrap'),
            array(),
            '',
            false
        );
        $this->_object->expects($this->any())->method('_createApplication')->will($this->returnValue($application));
        // invoke the constructor explicitly
        $this->_object->__construct(
            $settings,
            $this->_envBootstrap,
            $this->_docBlockBootstrap,
            $this->_profilerBootstrap,
            $this->_shell,
            __DIR__
        );
        $this->_object->expects(
            $this->any()
        )->method(
            '_createMemoryBootstrap'
        )->will(
            $this->returnValue($this->_memoryBootstrap)
        );
        return $application;
    }

    public function testGetApplication()
    {
        $application = $this->_object->getApplication();
        $this->assertInstanceOf('Magento\TestFramework\Application', $application);
        $this->assertStringStartsWith(__DIR__ . '/sandbox-mysql-', $application->getInstallDir());
        $this->assertInstanceOf('Magento\TestFramework\Db\Mysql', $application->getDbInstance());
        $this->assertSame($application, $this->_object->getApplication());
    }

    public function testGetDbVendorName()
    {
        $this->assertEquals('mysql', $this->_object->getDbVendorName());
    }

    public function testRunBootstrapEnvironment()
    {
        $this->_injectApplicationMock();
        $this->_envBootstrap->expects($this->once())->method('emulateHttpRequest')->with($this->identicalTo($_SERVER));
        $this->_envBootstrap->expects(
            $this->once()
        )->method(
            'emulateSession'
        )->with(
            $this->identicalTo(isset($_SESSION) ? $_SESSION : null)
        );
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapProfilerDisabled()
    {
        $this->_injectApplicationMock();
        $this->_profilerBootstrap->expects($this->never())->method($this->anything());
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapProfilerEnabled()
    {
        $baseDir = $this->_integrationTestsDir;
        $this->_injectApplicationMock(
            array(
                'TESTS_PROFILER_FILE' => 'profiler.csv',
                'TESTS_BAMBOO_PROFILER_FILE' => 'profiler_bamboo.csv',
                'TESTS_BAMBOO_PROFILER_METRICS_FILE' => 'profiler_metrics.php'
            )
        );
        $this->_profilerBootstrap->expects(
            $this->once()
        )->method(
            'registerFileProfiler'
        )->with(
            "{$baseDir}/profiler.csv"
        );
        $this->_profilerBootstrap->expects(
            $this->once()
        )->method(
            'registerBambooProfiler'
        )->with(
            "{$baseDir}/profiler_bamboo.csv",
            "{$baseDir}/profiler_metrics.php"
        );
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapMemoryWatch()
    {
        $this->_injectApplicationMock(array('TESTS_MEM_USAGE_LIMIT' => 100, 'TESTS_MEM_LEAK_LIMIT' => 60));
        $this->_object->expects(
            $this->once()
        )->method(
            '_createMemoryBootstrap'
        )->with(
            100,
            60
        )->will(
            $this->returnValue($this->_memoryBootstrap)
        );
        $this->_memoryBootstrap->expects($this->once())->method('activateStatsDisplaying');
        $this->_memoryBootstrap->expects($this->once())->method('activateLimitValidation');
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapDocBlockAnnotations()
    {
        $this->_injectApplicationMock();
        $this->_docBlockBootstrap->expects(
            $this->once()
        )->method(
            'registerAnnotations'
        )->with(
            $this->isInstanceOf('Magento\TestFramework\Application')
        );
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapAppCleanup()
    {
        $application = $this->_injectApplicationMock(array('TESTS_CLEANUP' => 'enabled'));
        $application->expects($this->once())->method('cleanup');
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapAppInitialize()
    {
        $application = $this->_injectApplicationMock();
        $application->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $application->expects($this->once())->method('initialize');
        $application->expects($this->never())->method('install');
        $application->expects($this->never())->method('cleanup');
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapAppInstall()
    {
        $adminUserName = \Magento\TestFramework\Bootstrap::ADMIN_NAME;
        $adminPassword = \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD;
        $adminRoleName = \Magento\TestFramework\Bootstrap::ADMIN_ROLE_NAME;
        $application = $this->_injectApplicationMock();
        $application->expects($this->once())->method('isInstalled')->will($this->returnValue(false));
        $application->expects($this->once())->method('install')->with($adminUserName, $adminPassword, $adminRoleName);
        $application->expects($this->never())->method('initialize');
        $application->expects($this->never())->method('cleanup');
        $this->_object->runBootstrap();
    }
}
