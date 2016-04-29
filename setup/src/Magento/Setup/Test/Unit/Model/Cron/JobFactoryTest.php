<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobFactoryTest extends \PHPUnit_Framework_TestCase
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
        $status = $this->getMock(\Magento\Setup\Model\Cron\Status::class, [], [], '', false);
        $status->expects($this->once())->method('getStatusFilePath')->willReturn('path_a');
        $status->expects($this->once())->method('getLogFilePath')->willReturn('path_b');
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->atLeastOnce())->method('get')->willReturn($this->objectManager);

        $upgradeCommand = $this->getMock(\Magento\Setup\Console\Command\UpgradeCommand::class, [], [], '', false);
        $moduleUninstaller = $this->getMock(\Magento\Setup\Model\ModuleUninstaller::class, [], [], '', false);
        $moduleRegistryUninstaller =
            $this->getMock(\Magento\Setup\Model\ModuleRegistryUninstaller::class, [], [], '', false);
        $moduleEnabler = $this->getMock(\Magento\Setup\Console\Command\ModuleEnableCommand::class, [], [], '', false);
        $moduleDisabler = $this->getMock(\Magento\Setup\Console\Command\ModuleDisableCommand::class, [], [], '', false);

        $updater = $this->getMock(\Magento\Setup\Model\Updater::class, [], [], '', false);
        $queue = $this->getMock(\Magento\Setup\Model\Cron\Queue::class, [], [], '', false);

        $returnValueMap = [
            [\Magento\Setup\Model\Updater::class, $updater],
            [\Magento\Setup\Model\Cron\Status::class, $status],
            [\Magento\Setup\Console\Command\UpgradeCommand::class, $upgradeCommand],
            [\Magento\Setup\Model\ObjectManagerProvider::class, $objectManagerProvider],
            [\Magento\Setup\Model\ModuleUninstaller::class, $moduleUninstaller],
            [\Magento\Setup\Model\ModuleRegistryUninstaller::class, $moduleRegistryUninstaller],
            [\Magento\Setup\Console\Command\ModuleDisableCommand::class, $moduleDisabler],
            [\Magento\Setup\Console\Command\ModuleEnableCommand::class, $moduleEnabler],
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
                $this->getMock(\Magento\Framework\App\State\CleanupFiles::class, [], [], '', false)
            ],
            [
                \Magento\Framework\App\Cache::class,
                $this->getMock(\Magento\Framework\App\Cache::class, [], [], '', false)
            ],
            [
                \Magento\Framework\Setup\BackupRollbackFactory::class,
                $this->getMock(\Magento\Framework\Setup\BackupRollbackFactory::class, [], [], '', false)
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
                $this->getMock(\Magento\Framework\Module\PackageInfoFactory::class, [], [], '', false)
            ],
            [
                \Magento\Framework\Composer\ComposerInformation::class,
                $this->getMock(\Magento\Framework\Composer\ComposerInformation::class, [], [], '', false)
            ],
            [
                \Magento\Theme\Model\Theme\ThemeUninstaller::class,
                $this->getMock(\Magento\Theme\Model\Theme\ThemeUninstaller::class, [], [], '', false)
            ],
            [
                \Magento\Theme\Model\Theme\ThemePackageInfo::class,
                $this->getMock(\Magento\Theme\Model\Theme\ThemePackageInfo::class, [], [], '', false)
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

    public function testModuleDisable()
    {
        $valueMap = [
            [
                \Magento\Framework\Module\PackageInfoFactory::class,
                $this->getMock(\Magento\Framework\Module\PackageInfoFactory::class, [], [], '', false)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\AbstractJob::class,
            $this->jobFactory->create('setup:module:disable', [])
        );
    }

    public function testModuleEnable()
    {
        $valueMap = [
            [
                \Magento\Framework\Module\PackageInfoFactory::class,
                $this->getMock(\Magento\Framework\Module\PackageInfoFactory::class, [], [], '', false)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            \Magento\Setup\Model\Cron\AbstractJob::class,
            $this->jobFactory->create('setup:module:enable', [])
        );
    }
}


// functions to override native php functions
namespace Magento\Setup\Model\Cron;

function fopen()
{
    return 'filestream';
}

function is_resource()
{
    return true;
}

function get_resource_type()
{
    return 'stream';
}
