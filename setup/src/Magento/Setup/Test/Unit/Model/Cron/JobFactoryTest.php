<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobFactory;

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
        $serviceManager = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', [], '', false);
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $status->expects($this->once())->method('getStatusFilePath')->willReturn('path_a');
        $status->expects($this->once())->method('getLogFilePath')->willReturn('path_b');
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->atLeastOnce())->method('get')->willReturn($this->objectManager);

        $upgradeCommand = $this->getMock('Magento\Setup\Console\Command\UpgradeCommand', [], [], '', false);
        $moduleUninstaller = $this->getMock('Magento\Setup\Model\ModuleUninstaller', [], [], '', false);
        $moduleRegistryUninstaller = $this->getMock('Magento\Setup\Model\ModuleRegistryUninstaller', [], [], '', false);
        $moduleEnabler = $this->getMock('Magento\Setup\Console\Command\ModuleEnableCommand', [], [], '', false);
        $moduleDisabler = $this->getMock('Magento\Setup\Console\Command\ModuleDisableCommand', [], [], '', false);

        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $queue = $this->getMock('Magento\Setup\Model\Cron\Queue', [], [], '', false);


        $returnValueMap = [
            ['Magento\Setup\Model\Updater', $updater],
            ['Magento\Setup\Model\Cron\Status', $status],
            ['Magento\Setup\Console\Command\UpgradeCommand', $upgradeCommand],
            ['Magento\Setup\Model\ObjectManagerProvider', $objectManagerProvider],
            ['Magento\Setup\Model\ModuleUninstaller', $moduleUninstaller],
            ['Magento\Setup\Model\ModuleRegistryUninstaller', $moduleRegistryUninstaller],
            ['Magento\Setup\Console\Command\ModuleDisableCommand', $moduleDisabler],
            ['Magento\Setup\Console\Command\ModuleEnableCommand', $moduleEnabler],
            ['Magento\Setup\Model\Cron\Queue', $queue]
        ];

        $serviceManager->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $this->jobFactory = new JobFactory($serviceManager);
    }

    public function testUpgrade()
    {
        $this->assertInstanceOf('Magento\Setup\Model\Cron\AbstractJob', $this->jobFactory->create('setup:upgrade', []));
    }

    public function testRollback()
    {
        $valueMap = [
            [
                'Magento\Framework\App\State\CleanupFiles',
                $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false)
            ],
            [
                'Magento\Framework\App\Cache',
                $this->getMock('Magento\Framework\App\Cache', [], [], '', false)
            ],
            [
                'Magento\Framework\Setup\BackupRollbackFactory',
                $this->getMock('Magento\Framework\Setup\BackupRollbackFactory', [], [], '', false)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            'Magento\Setup\Model\Cron\AbstractJob',
            $this->jobFactory->create('setup:rollback', [])
        );
    }

    public function testComponentUninstall()
    {
        $valueMap = [
            [
                'Magento\Framework\Module\PackageInfoFactory',
                $this->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false)
            ],
            [
                'Magento\Framework\Composer\ComposerInformation',
                $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false)
            ],
            [
                'Magento\Theme\Model\Theme\ThemeUninstaller',
                $this->getMock('Magento\Theme\Model\Theme\ThemeUninstaller', [], [], '', false)
            ],
            [
                'Magento\Theme\Model\Theme\ThemePackageInfo',
                $this->getMock('Magento\Theme\Model\Theme\ThemePackageInfo', [], [], '', false)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));
        $this->assertInstanceOf(
            'Magento\Setup\Model\Cron\JobComponentUninstall',
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
                'Magento\Framework\Module\PackageInfoFactory',
                $this->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            'Magento\Setup\Model\Cron\AbstractJob',
            $this->jobFactory->create('setup:module:disable', [])
        );
    }

    public function testModuleEnable()
    {
        $valueMap = [
            [
                'Magento\Framework\Module\PackageInfoFactory',
                $this->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false)
            ],
        ];
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->assertInstanceOf(
            'Magento\Setup\Model\Cron\AbstractJob',
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
