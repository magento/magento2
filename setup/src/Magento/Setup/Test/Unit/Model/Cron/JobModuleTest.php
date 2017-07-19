<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobModule;

class JobModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteModuleDisable()
    {
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->getMock(\Magento\Framework\App\State\CleanupFiles::class, [], [], '', false);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->getMock(\Magento\Framework\App\Cache::class, [], [], '', false);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            [\Magento\Framework\Module\PackageInfoFactory::class],
            [\Magento\Framework\App\State\CleanupFiles::class, $cleanupFiles],
            [\Magento\Framework\App\Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->getMock(\Magento\Setup\Console\Command\ModuleDisableCommand::class, [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock(\Magento\Setup\Model\Cron\Status::class, [], [], '', false);
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
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->getMock(\Magento\Framework\App\State\CleanupFiles::class, [], [], '', false);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->getMock(\Magento\Framework\App\Cache::class, [], [], '', false);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            [\Magento\Framework\Module\PackageInfoFactory::class],
            [\Magento\Framework\App\State\CleanupFiles::class, $cleanupFiles],
            [\Magento\Framework\App\Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->getMock(\Magento\Setup\Console\Command\ModuleEnableCommand::class, [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock(\Magento\Setup\Model\Cron\Status::class, [], [], '', false);
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
