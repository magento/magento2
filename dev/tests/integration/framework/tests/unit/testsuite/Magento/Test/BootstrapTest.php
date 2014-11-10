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
     * @var \Magento\TestFramework\Bootstrap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * Setting values required to be specified
     *
     * @var array
     */
    protected $_requiredSettings = array(
        'TESTS_INSTALL_CONFIG_FILE' => 'etc/install-config-mysql.php',
    );

    /**
     * @var \Magento\TestFramework\Bootstrap\Settings|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_settings;

    /**
     * @var \Magento\TestFramework\Bootstrap\Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_envBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\DocBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_docBlockBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\Profiler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_profilerBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\MemoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $memoryFactory;

    /**
     * @var \Magento\Framework\Shell|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    private $application;

    /**
     * @var string
     */
    protected $_integrationTestsDir;

    protected function setUp()
    {
        $this->_integrationTestsDir = realpath(__DIR__ . '/../../../../../../');
        $this->_settings = $this->getMock('\Magento\TestFramework\Bootstrap\Settings', [], [], '', false);
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
        $this->_shell = $this->getMock('Magento\Framework\Shell', array('execute'), array(), '', false);
        $this->application = $this->getMock('\Magento\TestFramework\Application', [], [], '', false);
        $this->memoryFactory = $this->getMock('\Magento\TestFramework\Bootstrap\MemoryFactory', [], [], '', false);
        $this->_object = new \Magento\TestFramework\Bootstrap(
            $this->_settings,
            $this->_envBootstrap,
            $this->_docBlockBootstrap,
            $this->_profilerBootstrap,
            $this->_shell,
            $this->application,
            $this->memoryFactory
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

    public function testGetApplication()
    {
        $this->assertSame($this->application, $this->_object->getApplication());
    }

    public function testRunBootstrap()
    {
        $this->_envBootstrap->expects($this->once())
            ->method('emulateHttpRequest')
            ->with($this->identicalTo($_SERVER))
        ;
        $this->_envBootstrap->expects($this->once())
            ->method('emulateSession')
            ->with($this->identicalTo(isset($_SESSION) ? $_SESSION : null))
        ;

        $memUsageLimit = '100B';
        $memLeakLimit = '60B';
        $settingsMap = [
            ['TESTS_MEM_USAGE_LIMIT', 0, $memUsageLimit],
            ['TESTS_MEM_LEAK_LIMIT', 0, $memLeakLimit],
        ];
        $this->_settings->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($settingsMap));
        $memoryBootstrap = $this->getMock(
            'Magento\TestFramework\Bootstrap\Memory',
            array('activateStatsDisplaying', 'activateLimitValidation'),
            array(),
            '',
            false
        );
        $memoryBootstrap->expects($this->once())->method('activateStatsDisplaying');
        $memoryBootstrap->expects($this->once())->method('activateLimitValidation');
        $this->memoryFactory->expects($this->once())
            ->method('create')
            ->with($memUsageLimit, $memLeakLimit)
            ->will($this->returnValue($memoryBootstrap));

        $this->_docBlockBootstrap->expects($this->once())
            ->method('registerAnnotations')
            ->with($this->isInstanceOf('Magento\TestFramework\Application'))
        ;

        $this->_profilerBootstrap->expects($this->never())->method($this->anything());

        $this->_object->runBootstrap();
    }

    public function testRunBootstrapProfilerEnabled()
    {
        $memoryBootstrap = $this->getMock(
            'Magento\TestFramework\Bootstrap\Memory',
            array('activateStatsDisplaying', 'activateLimitValidation'),
            array(),
            '',
            false
        );
        $memoryBootstrap->expects($this->once())->method('activateStatsDisplaying');
        $memoryBootstrap->expects($this->once())->method('activateLimitValidation');
        $this->memoryFactory->expects($this->once())
            ->method('create')
            ->with(0, 0)
            ->will($this->returnValue($memoryBootstrap));

        $settingsMap = [
            ['TESTS_PROFILER_FILE', '', 'profiler.csv'],
            ['TESTS_BAMBOO_PROFILER_FILE', '', 'profiler_bamboo.csv'],
            ['TESTS_BAMBOO_PROFILER_METRICS_FILE', '', 'profiler_metrics.php'],
        ];
        $this->_settings->expects($this->any())
            ->method('getAsFile')
            ->will($this->returnValueMap($settingsMap));
        $this->_profilerBootstrap
            ->expects($this->once())
            ->method('registerFileProfiler')
            ->with("profiler.csv")
        ;
        $this->_profilerBootstrap
            ->expects($this->once())
            ->method('registerBambooProfiler')
            ->with("profiler_bamboo.csv", "profiler_metrics.php")
        ;
        $this->_object->runBootstrap();
    }
}
