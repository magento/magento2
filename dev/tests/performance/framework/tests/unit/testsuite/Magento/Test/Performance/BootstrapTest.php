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

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $appBootstrap;

    protected function setUp()
    {
        $this->appBootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        $dirList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $dirList->expects($this->any())->method('getRoot')->will($this->returnValue(BP));
        $this->appBootstrap->expects($this->any())->method('getDirList')->will($this->returnValue($dirList));
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManager');
        $this->appBootstrap->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManager));
    }

    protected function tearDown()
    {
        // Delete a directory, where tests do some temporary work
        $tmpDir = $this->_getBaseFixtureDir() . '/config_dist/tmp';
        $filesystemAdapter = new \Magento\Framework\Filesystem\Driver\File();
        if ($filesystemAdapter->isExists($tmpDir)) {
            $filesystemAdapter->deleteDirectory($tmpDir);
        }
    }

    /**
     * @param string $fixtureDir
     * @param string $expectedUrl
     * @dataProvider configLoadDataProvider
     */
    public function testConfigLoad($fixtureDir, $expectedUrl)
    {
        $bootstrap = new \Magento\TestFramework\Performance\Bootstrap(
            $this->appBootstrap,
            $fixtureDir
        );
        $config = $bootstrap->getConfig();
        $this->assertInstanceOf('Magento\TestFramework\Performance\Config', $config);
        $this->assertEquals($expectedUrl, $config->getApplicationUrlHost());
    }

    /**
     * @return array
     */
    public function configLoadDataProvider()
    {
        $baseFixtureDir = $this->_getBaseFixtureDir();
        return array(
            'config.php.dist' => array('fixtureDir' => $baseFixtureDir . '/config_dist', 'expectedUrl' => '127.0.0.1'),
            'config.dist' => array('fixtureDir' => $baseFixtureDir . '/config_normal', 'expectedUrl' => '192.168.0.1')
        );
    }

    /**
     * Return path to directory, utilized for bootstrap
     *
     * @return string
     */
    protected function _getBaseFixtureDir()
    {
        return __DIR__ . '/_files/bootstrap';
    }

    public function testCleanupReportsCreatesDirectory()
    {
        $fixtureDir = $this->_getBaseFixtureDir() . '/config_dist';
        $bootstrap = new \Magento\TestFramework\Performance\Bootstrap($this->appBootstrap, $fixtureDir);

        $reportDir = $fixtureDir . '/tmp/subdirectory/report';

        $this->assertFalse(is_dir($reportDir));
        $bootstrap->cleanupReports();
        $this->assertTrue(is_dir($reportDir));
    }

    public function testCleanupReportsRemovesFiles()
    {
        $fixtureDir = $this->_getBaseFixtureDir() . '/config_dist';
        $bootstrap = new \Magento\TestFramework\Performance\Bootstrap($this->appBootstrap, $fixtureDir);

        $reportDir = $fixtureDir . '/tmp/subdirectory/report';
        mkdir($reportDir, 0777, true);
        $reportFile = $reportDir . '/a.jtl';
        touch($reportFile);

        $this->assertFileExists($reportFile);
        $bootstrap->cleanupReports();
        $this->assertFileNotExists($reportFile);
    }

    public function testCreateApplicationTestSuite()
    {
        $shell = $this->getMock('Magento\Framework\Shell', [], [], '', false);
        $bootstrap = new \Magento\TestFramework\Performance\Bootstrap(
            $this->appBootstrap,
            $this->_getBaseFixtureDir() . '/config_dist'
        );
        $application = $bootstrap->createApplication($shell);
        $this->assertInstanceOf('Magento\TestFramework\Application', $application);
        $handler = $this->getMockForAbstractClass('Magento\TestFramework\Performance\Scenario\HandlerInterface');
        $testSuite = $bootstrap->createTestSuite($application, $handler);
        $this->assertInstanceOf('Magento\TestFramework\Performance\Testsuite', $testSuite);
    }
}
