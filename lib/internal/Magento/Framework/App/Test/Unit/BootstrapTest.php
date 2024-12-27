<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\AppInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BootstrapTest extends TestCase
{
    /**
     * @var AppInterface|MockObject
     */
    protected $application;

    /**
     * @var ObjectManagerFactory|MockObject
     */
    protected $objectManagerFactory;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManager;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var DirectoryList|MockObject
     */
    protected $dirs;

    /**
     * @var ReadInterface|MockObject
     */
    protected $configDir;

    /**
     * @var MaintenanceMode|MockObject
     */
    protected $maintenanceMode;

    /**
     * @var MockObject
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Bootstrap|MockObject
     */
    protected $bootstrapMock;

    /**
     * @var RemoteAddress|MockObject
     */
    protected $remoteAddress;

    protected function setUp(): void
    {
        $this->objectManagerFactory = $this->createMock(ObjectManagerFactory::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->dirs = $this->createPartialMock(DirectoryList::class, ['getPath']);
        $this->maintenanceMode = $this->createPartialMock(MaintenanceMode::class, ['isOn']);
        $this->remoteAddress = $this->createMock(RemoteAddress::class);
        $filesystem = $this->createMock(Filesystem::class);

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);

        $mapObjectManager = [
            [DirectoryList::class, $this->dirs],
            [MaintenanceMode::class, $this->maintenanceMode],
            [RemoteAddress::class, $this->remoteAddress],
            [Filesystem::class, $filesystem],
            [DeploymentConfig::class, $this->deploymentConfig],
            [LoggerInterface::class, $this->logger],
        ];

        $this->objectManager->expects($this->any())->method('get')
            ->willReturnMap($mapObjectManager);

        $this->configDir = $this->getMockForAbstractClass(ReadInterface::class);

        $filesystem->expects($this->any())->method('getDirectoryRead')
            ->willReturn($this->configDir);

        $this->application = $this->getMockForAbstractClass(AppInterface::class);

        $this->objectManager->expects($this->any())->method('create')
            ->willReturn($this->application);

        $this->objectManagerFactory->expects($this->any())->method('create')
            ->willReturn($this->objectManager);

        $this->bootstrapMock = $this->getMockBuilder(Bootstrap::class)
            ->onlyMethods(['assertMaintenance', 'assertInstalled', 'getIsExpected', 'isInstalled', 'terminate'])
            ->setConstructorArgs([$this->objectManagerFactory, '', ['value1', 'value2']])
            ->getMock();
    }

    public function testCreateObjectManagerFactory()
    {
        $result = Bootstrap::createObjectManagerFactory('test', []);
        $this->assertInstanceOf(ObjectManagerFactory::class, $result);
    }

    public function testCreateFilesystemDirectoryList()
    {
        $result = Bootstrap::createFilesystemDirectoryList(
            'test',
            [Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [DirectoryList::APP => ['path' => '/custom/path']]]
        );
        /** @var DirectoryList $result */
        $this->assertInstanceOf(DirectoryList::class, $result);
        $this->assertEquals('/custom/path', $result->getPath(DirectoryList::APP));
    }

    public function testCreateFilesystemDriverPool()
    {
        $driverClass = get_class($this->getMockForAbstractClass(DriverInterface::class));
        $result = Bootstrap::createFilesystemDriverPool(
            [Bootstrap::INIT_PARAM_FILESYSTEM_DRIVERS => ['custom' => $driverClass]]
        );
        /** @var DriverPool $result */
        $this->assertInstanceOf(DriverPool::class, $result);
        $this->assertInstanceOf($driverClass, $result->getDriver('custom'));
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

    /**
     * @param $modeFromEnvironment
     * @param $modeFromDeployment
     * @param $isDeveloper
     *
     * @dataProvider testIsDeveloperModeDataProvider
     */
    public function testIsDeveloperMode($modeFromEnvironment, $modeFromDeployment, $isDeveloper)
    {
        $testParams = [];
        if ($modeFromEnvironment) {
            $testParams[State::PARAM_MODE] = $modeFromEnvironment;
        }
        if ($modeFromDeployment) {
            $this->deploymentConfig->method('get')->willReturn($modeFromDeployment);
        }
        $bootstrap = self::createBootstrap($testParams);
        $this->assertEquals($isDeveloper, $bootstrap->isDeveloperMode());
    }

    /**
     * @return array
     */
    public static function testIsDeveloperModeDataProvider()
    {
        return [
            [null, null, false],
            [State::MODE_DEVELOPER, State::MODE_PRODUCTION, true],
            [State::MODE_PRODUCTION, State::MODE_DEVELOPER, false],
            [null, State::MODE_DEVELOPER, true],
            [null, State::MODE_PRODUCTION, false]
        ];
    }

    public function testRunNoErrors()
    {
        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')->willReturn(null);
        $this->bootstrapMock->expects($this->once())->method('assertInstalled')->willReturn(null);
        $this->application->expects($this->once())->method('launch')->willReturn($responseMock);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithMaintenanceErrors()
    {
        $expectedException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')
            ->willThrowException($expectedException);
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithInstallErrors()
    {
        $expectedException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')->willReturn(null);
        $this->bootstrapMock->expects($this->once())->method('assertInstalled')
            ->willThrowException($expectedException);
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithBothErrors()
    {
        $expectedMaintenanceException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')
            ->willThrowException($expectedMaintenanceException);
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
        $this->remoteAddress->expects($this->once())->method('getRemoteAddress')->willReturn(false);
        $this->application->expects($this->never())->method('launch');
        $this->application->expects($this->once())->method('catchException')->willReturn(true);
        $bootstrap->run($this->application);
        $this->assertEquals(Bootstrap::ERR_MAINTENANCE, $bootstrap->getErrorCode());
    }

    /**
     * @return array
     */
    public static function assertMaintenanceDataProvider()
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
    public static function assertInstalledDataProvider()
    {
        return [
            [false, true],
            [true, false],
        ];
    }

    /**
     * Restore error handler after Bootstrap->run method
     */
    protected function tearDown(): void
    {
        restore_error_handler();
        setCustomErrorHandler();
    }
}
