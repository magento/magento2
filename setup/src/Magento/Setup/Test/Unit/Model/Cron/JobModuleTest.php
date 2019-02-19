<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobModule;

class JobModuleTest extends \PHPUnit\Framework\TestCase
{
    public function testExecuteModuleDisable()
    {
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(\Magento\Framework\App\State\CleanupFiles::class);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->createMock(\Magento\Framework\App\Cache::class);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            [\Magento\Framework\Module\PackageInfoFactory::class],
            [\Magento\Framework\App\State\CleanupFiles::class, $cleanupFiles],
            [\Magento\Framework\App\Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->createMock(\Magento\Setup\Console\Command\ModuleDisableCommand::class);
        $command->expects($this->once())->method('run');
        $status = $this->createMock(\Magento\Setup\Model\Cron\Status::class);
        $status->expects($this->atLeastOnce())->method('add');
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $params['components'][] = ['name' => 'vendor/module'];
        $jobModuleDisable = new JobModule(
            $command,
            $objectManagerProvider,
            $output,
            $status,
            'setup:module:disable',
            $params
        );
        $jobModuleDisable->execute();
    }

    public function testExecuteModuleEnable()
    {
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(\Magento\Framework\App\State\CleanupFiles::class);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->createMock(\Magento\Framework\App\Cache::class);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            [\Magento\Framework\Module\PackageInfoFactory::class],
            [\Magento\Framework\App\State\CleanupFiles::class, $cleanupFiles],
            [\Magento\Framework\App\Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->createMock(\Magento\Setup\Console\Command\ModuleEnableCommand::class);
        $command->expects($this->once())->method('run');
        $status = $this->createMock(\Magento\Setup\Model\Cron\Status::class);
        $status->expects($this->atLeastOnce())->method('add');
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $params['components'][] = ['name' => 'vendor/module'];
        $jobModuleEnable = new JobModule(
            $command,
            $objectManagerProvider,
            $output,
            $status,
            'setup:module:enable',
            $params
        );
        $jobModuleEnable->execute();
    }
}
