<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\Bootstrap;
use \Magento\Framework\App\State;
use \Magento\Framework\App\MaintenanceMode;

use Magento\Framework\App\Filesystem\DirectoryList;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\AppInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;

    /**
     * @var \Magento\Framework\App\ObjectManagerFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerFactory;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Psr\Log\LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dirs;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configDir;

    /**
     * @var MaintenanceMode | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $maintenanceMode;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Bootstrap | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bootstrapMock;

    public function setUp()
    {
        $this->objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->dirs = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', ['getPath'], [], '', false);
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', ['isOn'], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);

        $mapObjectManager = [
            ['Magento\Framework\App\Filesystem\DirectoryList', $this->dirs],
            ['Magento\Framework\App\MaintenanceMode', $this->maintenanceMode],
            ['Magento\Framework\Filesystem', $filesystem],
            ['Magento\Framework\App\DeploymentConfig', $this->deploymentConfig],
            ['Psr\Log\LoggerInterface', $this->logger],
        ];

        $this->objectManager->expects($this->any())->method('get')
            ->will(($this->returnValueMap($mapObjectManager)));

        $this->configDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');

        $filesystem->expects($this->any())->method('getDirectoryRead')
            ->will(($this->returnValue($this->configDir)));

        $this->application = $this->getMockForAbstractClass('Magento\Framework\AppInterface');

        $this->objectManager->expects($this->any())->method('create')
            ->will(($this->returnValue($this->application)));

        $this->objectManagerFactory->expects($this->any())->method('create')
            ->will(($this->returnValue($this->objectManager)));

        $this->bootstrapMock = $this->getMock('Magento\Framework\App\Bootstrap',
            ['assertMaintenance', 'assertInstalled', 'getIsExpected', 'isInstalled', 'terminate'],
            [$this->objectManagerFactory, '', ['value1', 'value2']]
        );
    }

    public function testCreateObjectManagerFactory()
    {
        $result = Bootstrap::createObjectManagerFactory('test', []);
        $this->assertInstanceOf('Magento\Framework\App\ObjectManagerFactory', $result);
    }

    public function testCreateFilesystemDirectoryList()
    {
        $result = Bootstrap::createFilesystemDirectoryList(
            'test',
            [Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [DirectoryList::APP => ['path' => '/custom/path']]]
        );
        /** @var \Magento\Framework\App\Filesystem\DirectoryList $result */
        $this->assertInstanceOf('Magento\Framework\App\Filesystem\DirectoryList', $result);
        $this->assertEquals('/custom/path', $result->getPath(DirectoryList::APP));
    }

    public function testCreateFilesystemDriverPool()
    {
        $driverClass = get_class($this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface'));
        $result = Bootstrap::createFilesystemDriverPool(
            [Bootstrap::INIT_PARAM_FILESYSTEM_DRIVERS => ['custom' => $driverClass]]
        );
        /** @var \Magento\Framework\Filesystem\DriverPool $result */
        $this->assertInstanceOf('Magento\Framework\Filesystem\DriverPool', $result);
        $this->assertInstanceof($driverClass, $result->getDriver('custom'));
    }

    public function testGetParams()
    {
        $testParams = ['testValue1', 'testValue2'];
        $bootstrap = self::createBootstrap($testParams);
        $this->assertSame($testParams, $bootstrap->getParams());
    }

    /**
     * Creates a bootstrap object
     *
     * @param array $testParams
     * @return Bootstrap
     */
    private function createBootstrap($testParams = ['value1', 'value2'])
    {
        return new Bootstrap($this->objectManagerFactory, '', $testParams);
    }

    public function testCreateApplication()
    {
        $bootstrap = self::createBootstrap();
        $testArgs = ['arg1', 'arg2'];
        $this->assertSame($this->application, $bootstrap->createApplication('someApplicationType', $testArgs));
    }

    public function testGetObjectManager()
    {
        $bootstrap = self::createBootstrap();
        $this->assertSame($this->objectManager, $bootstrap->getObjectManager());
    }

    public function testIsDeveloperMode()
    {
        $bootstrap = self::createBootstrap();
        $this->assertFalse($bootstrap->isDeveloperMode());
        $testParams = [State::PARAM_MODE => State::MODE_DEVELOPER];
        $bootstrap = self::createBootstrap($testParams);
        $this->assertTrue($bootstrap->isDeveloperMode());
        $this->deploymentConfig->expects($this->any())->method('get')->willReturn(State::MODE_DEVELOPER);
        $bootstrap = self::createBootstrap();
        $this->assertTrue($bootstrap->isDeveloperMode());
    }

    public function testIsDeveloperModeСontradictoryValues()
    {
        $this->deploymentConfig->expects($this->any())->method('get')->willReturn(State::MODE_PRODUCTION);
        $bootstrap = self::createBootstrap();
        $this->assertFalse($bootstrap->isDeveloperMode());
        $testParams = [State::PARAM_MODE => State::MODE_DEVELOPER];
        $bootstrap = self::createBootstrap($testParams);
        $this->assertTrue($bootstrap->isDeveloperMode());
    }

    public function testRunNoErrors()
    {
        $responseMock = $this->getMockForAbstractClass('\Magento\Framework\App\ResponseInterface');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')->will($this->returnValue(null));
        $this->bootstrapMock->expects($this->once())->method('assertInstalled')->will($this->returnValue(null));
        $this->application->expects($this->once())->method('launch')->willReturn($responseMock);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithMaintenanceErrors()
    {
        $expectedException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')
            ->will($this->throwException($expectedException));
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithInstallErrors()
    {
        $expectedException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')->will($this->returnValue(null));
        $this->bootstrapMock->expects($this->once())->method('assertInstalled')
            ->will($this->throwException($expectedException));
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithBothErrors()
    {
        $expectedMaintenanceException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')
            ->will($this->throwException($expectedMaintenanceException));
        $this->bootstrapMock->expects($this->never())->method('assertInstalled');
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedMaintenanceException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    /**
     * @param bool $isOn
     * @param bool $isExpected
     *
     * @dataProvider assertMaintenanceDataProvider
     */
    public function testAssertMaintenance($isOn, $isExpected)
    {
        $bootstrap = self::createBootstrap([Bootstrap::PARAM_REQUIRE_MAINTENANCE => $isExpected]);
        $this->maintenanceMode->expects($this->once())->method('isOn')->willReturn($isOn);
        $this->application->expects($this->never())->method('launch');
        $this->application->expects($this->once())->method('catchException')->willReturn(true);
        $bootstrap->run($this->application);
        $this->assertEquals(Bootstrap::ERR_MAINTENANCE, $bootstrap->getErrorCode());
    }

    /**
     * @return array
     */
    public function assertMaintenanceDataProvider()
    {
        return [
            [true, false],
            [false, true]
        ];
    }

    /**
     * @param bool $isInstalled
     * @param bool $isExpected
     *
     * @dataProvider assertInstalledDataProvider
     */
    public function testAssertInstalled($isInstalled, $isExpected)
    {
        $bootstrap = self::createBootstrap([Bootstrap::PARAM_REQUIRE_IS_INSTALLED => $isExpected]);
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn($isInstalled);
        $this->application->expects($this->never())->method('launch');
        $this->application->expects($this->once())->method('catchException')->willReturn(true);
        $bootstrap->run($this->application);
        $this->assertEquals(Bootstrap::ERR_IS_INSTALLED, $bootstrap->getErrorCode());
    }

    /**
     * @return array
     */
    public function assertInstalledDataProvider()
    {
        return [
            [false, true],
            [true, false],
        ];
    }
}
