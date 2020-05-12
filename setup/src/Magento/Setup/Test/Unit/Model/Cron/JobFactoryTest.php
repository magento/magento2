<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Backend\Console\Command\CacheDisableCommand;
use Magento\Backend\Console\Command\CacheEnableCommand;
use Magento\Framework\App\Cache;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Console\Command\MaintenanceDisableCommand;
use Magento\Setup\Console\Command\MaintenanceEnableCommand;
use Magento\Setup\Console\Command\ModuleDisableCommand;
use Magento\Setup\Console\Command\ModuleEnableCommand;
use Magento\Setup\Console\Command\UpgradeCommand;
use Magento\Setup\Model\Cron\AbstractJob;
use Magento\Setup\Model\Cron\JobComponentUninstall;
use Magento\Setup\Model\Cron\JobFactory;
use Magento\Setup\Model\Cron\JobSetCache;
use Magento\Setup\Model\Cron\JobSetMaintenanceMode;
use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ModuleRegistryUninstaller;
use Magento\Setup\Model\ModuleUninstaller;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\Updater;
use Magento\Theme\Model\Theme\ThemePackageInfo;
use Magento\Theme\Model\Theme\ThemeUninstaller;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobFactoryTest extends TestCase
{
    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var JobFactory
     */
    private $jobFactory;

    protected function setUp(): void
    {
        $serviceManager =
            $this->getMockForAbstractClass(ServiceLocatorInterface::class, [], '', false);
        $status = $this->createMock(Status::class);
        $status->expects($this->once())->method('getStatusFilePath')->willReturn('path_a');
        $status->expects($this->once())->method('getLogFilePath')->willReturn('path_b');
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->atLeastOnce())->method('get')->willReturn($this->objectManager);

        $upgradeCommand = $this->createMock(UpgradeCommand::class);
        $moduleUninstaller = $this->createMock(ModuleUninstaller::class);
        $moduleRegistryUninstaller =
            $this->createMock(ModuleRegistryUninstaller::class);
        $moduleEnabler = $this->createMock(ModuleEnableCommand::class);
        $moduleDisabler = $this->createMock(ModuleDisableCommand::class);
        $maintenanceDisabler = $this->createMock(MaintenanceDisableCommand::class);
        $maintenanceEnabler = $this->createMock(MaintenanceEnableCommand::class);

        $updater = $this->createMock(Updater::class);
        $queue = $this->createMock(Queue::class);

        $returnValueMap = [
            [Updater::class, $updater],
            [Status::class, $status],
            [UpgradeCommand::class, $upgradeCommand],
            [ObjectManagerProvider::class, $objectManagerProvider],
            [ModuleUninstaller::class, $moduleUninstaller],
            [ModuleRegistryUninstaller::class, $moduleRegistryUninstaller],
            [ModuleDisableCommand::class, $moduleDisabler],
            [ModuleEnableCommand::class, $moduleEnabler],
            [MaintenanceDisableCommand::class, $maintenanceDisabler],
            [MaintenanceEnableCommand::class, $maintenanceEnabler],
            [Queue::class, $queue]
        ];

        $serviceManager->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap($returnValueMap);

        $this->jobFactory = new JobFactory($serviceManager);
    }

    public function testUpgrade()
    {
        $this->assertInstanceOf(
            AbstractJob::class,
            $this->jobFactory->create('setup:upgrade', [])
        );
    }

    public function testRollback()
    {
        $valueMap = [
            [
                CleanupFiles::class,
                $this->createMock(CleanupFiles::class)
            ],
            [
                Cache::class,
                $this->createMock(Cache::class)
            ],
            [
                BackupRollbackFactory::class,
                $this->createMock(BackupRollbackFactory::class)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap($valueMap);

        $this->assertInstanceOf(
            AbstractJob::class,
            $this->jobFactory->create('setup:rollback', [])
        );
    }

    public function testComponentUninstall()
    {
        $valueMap = [
            [
                PackageInfoFactory::class,
                $this->createMock(PackageInfoFactory::class)
            ],
            [
                ComposerInformation::class,
                $this->createMock(ComposerInformation::class)
            ],
            [
                ThemeUninstaller::class,
                $this->createMock(ThemeUninstaller::class)
            ],
            [
                ThemePackageInfo::class,
                $this->createMock(ThemePackageInfo::class)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap($valueMap);
        $this->assertInstanceOf(
            JobComponentUninstall::class,
            $this->jobFactory->create('setup:component:uninstall', [])
        );
    }

    public function testCreateUnknownJob()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('job is not supported');
        $this->jobFactory->create('unknown', []);
    }

    public function testCacheEnable()
    {
        $valueMap = [
            [
                CacheEnableCommand::class,
                $this->getMockBuilder(CacheEnableCommand::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]
        ];

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap($valueMap);

        $this->assertInstanceOf(
            JobSetCache::class,
            $this->jobFactory->create('setup:cache:enable', [])
        );
    }

    public function testCacheDisable()
    {
        $valueMap = [
            [
                CacheDisableCommand::class,
                $this->getMockBuilder(CacheDisableCommand::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]
        ];
        $this->objectManager->expects($this->any())->method('get')->willReturnMap($valueMap);

        $this->assertInstanceOf(
            JobSetCache::class,
            $this->jobFactory->create('setup:cache:disable', [])
        );
    }

    public function testMaintenanceModeEnable()
    {
        $this->assertInstanceOf(
            JobSetMaintenanceMode::class,
            $this->jobFactory->create(JobFactory::JOB_MAINTENANCE_MODE_ENABLE, [])
        );
    }

    public function testMaintenanceModeDisable()
    {
        $this->assertInstanceOf(
            JobSetMaintenanceMode::class,
            $this->jobFactory->create(JobFactory::JOB_MAINTENANCE_MODE_DISABLE, [])
        );
    }
}

// functions to override native php functions
namespace Magento\Setup\Model\Cron;

/**
 * @return string
 */
function fopen()
{
    return 'filestream';
}

/**
 * @return bool
 */
function is_resource()
{
    return true;
}

/**
 * @return string
 */
function get_resource_type()
{
    return 'stream';
}
