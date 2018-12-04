<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Backend\Console\Command\CacheDisableCommand;
use Magento\Backend\Console\Command\CacheEnableCommand;
use Magento\Setup\Console\Command\MaintenanceDisableCommand;
use Magento\Setup\Console\Command\MaintenanceEnableCommand;
use Magento\Setup\Model\Cron\JobFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var JobFactory
     */
    private $jobFactory;

    public function setUp()
    {
        $serviceManager =
            $this->getMockForAbstractClass(\Zend\ServiceManager\ServiceLocatorInterface::class, [], '', false);
        $status = $this->createMock(\Magento\Setup\Model\Cron\Status::class);
        $status->expects($this->once())->method('getStatusFilePath')->willReturn('path_a');
        $status->expects($this->once())->method('getLogFilePath')->willReturn('path_b');
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->atLeastOnce())->method('get')->willReturn($this->objectManager);

        $upgradeCommand = $this->createMock(\Magento\Setup\Console\Command\UpgradeCommand::class);
        $moduleUninstaller = $this->createMock(\Magento\Setup\Model\ModuleUninstaller::class);
        $moduleRegistryUninstaller =
            $this->createMock(\Magento\Setup\Model\ModuleRegistryUninstaller::class);
        $moduleEnabler = $this->createMock(\Magento\Setup\Console\Command\ModuleEnableCommand::class);
        $moduleDisabler = $this->createMock(\Magento\Setup\Console\Command\ModuleDisableCommand::class);
        $maintenanceDisabler = $this->createMock(MaintenanceDisableCommand::class);
        $maintenanceEnabler = $this->createMock(MaintenanceEnableCommand::class);

        $updater = $this->createMock(\Magento\Setup\Model\Updater::class);
        $queue = $this->createMock(\Magento\Setup\Model\Cron\Queue::class);

        $returnValueMap = [
            [\Magento\Setup\Model\Updater::class, $updater],
            [\Magento\Setup\Model\Cron\Status::class, $status],
            [\Magento\Setup\Console\Command\UpgradeCommand::class, $upgradeCommand],
            [\Magento\Setup\Model\ObjectManagerProvider::class, $objectManagerProvider],
            [\Magento\Setup\Model\ModuleUninstaller::class, $moduleUninstaller],
            [\Magento\Setup\Model\ModuleRegistryUninstaller::class, $moduleRegistryUninstaller],
            [\Magento\Setup\Console\Command\ModuleDisableCommand::class, $moduleDisabler],
            [\Magento\Setup\Console\Command\ModuleEnableCommand::class, $moduleEnabler],
            [MaintenanceDisableCommand::class, $maintenanceDisabler],
            [MaintenanceEnableCommand::class, $maintenanceEnabler],
            [\Magento\Setup\Model\Cron\Queue::class, $queue]
        ];

        $serviceManager->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $this->jobFactory = new JobFactory($serviceManager);
    }

    public function testUpgrade()
    {
        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\AbstractJob::class,
            $this->jobFactory->create('setup:upgrade', [])
        );
    }

    public function testRollback()
    {
        $valueMap = [
            [
                \Magento\Framework\App\State\CleanupFiles::class,
                $this->createMock(\Magento\Framework\App\State\CleanupFiles::class)
            ],
            [
                \Magento\Framework\App\Cache::class,
                $this->createMock(\Magento\Framework\App\Cache::class)
            ],
            [
                \Magento\Framework\Setup\BackupRollbackFactory::class,
                $this->createMock(\Magento\Framework\Setup\BackupRollbackFactory::class)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\AbstractJob::class,
            $this->jobFactory->create('setup:rollback', [])
        );
    }

    public function testComponentUninstall()
    {
        $valueMap = [
            [
                \Magento\Framework\Module\PackageInfoFactory::class,
                $this->createMock(\Magento\Framework\Module\PackageInfoFactory::class)
            ],
            [
                \Magento\Framework\Composer\ComposerInformation::class,
                $this->createMock(\Magento\Framework\Composer\ComposerInformation::class)
            ],
            [
                \Magento\Theme\Model\Theme\ThemeUninstaller::class,
                $this->createMock(\Magento\Theme\Model\Theme\ThemeUninstaller::class)
            ],
            [
                \Magento\Theme\Model\Theme\ThemePackageInfo::class,
                $this->createMock(\Magento\Theme\Model\Theme\ThemePackageInfo::class)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));
        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\JobComponentUninstall::class,
            $this->jobFactory->create('setup:component:uninstall', [])
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage job is not supported
     */
    public function testCreateUnknownJob()
    {
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
            ->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\JobSetCache::class,
            $this->jobFactory->create('setup:cache:enable', [])
        );
    }

    public function testCacheDisable()
    {
        $valueMap = [
            [
                \Magento\Backend\Console\Command\CacheDisableCommand::class,
                $this->getMockBuilder(CacheDisableCommand::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]
        ];
        $this->objectManager->expects($this->any())->method('get')->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\JobSetCache::class,
            $this->jobFactory->create('setup:cache:disable', [])
        );
    }

    public function testMaintenanceModeEnable()
    {
        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\JobSetMaintenanceMode::class,
            $this->jobFactory->create(JobFactory::JOB_MAINTENANCE_MODE_ENABLE, [])
        );
    }

    public function testMaintenanceModeDisable()
    {
        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\JobSetMaintenanceMode::class,
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
