<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobModule;

class JobModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteModuleDisable()
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            ['Magento\Framework\Module\PackageInfoFactory'],
            ['Magento\Framework\App\State\CleanupFiles', $cleanupFiles],
            ['Magento\Framework\App\Cache', $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->getMock('Magento\Setup\Console\Command\ModuleDisableCommand', [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $status->expects($this->atLeastOnce())->method('add');
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
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
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            ['Magento\Framework\Module\PackageInfoFactory'],
            ['Magento\Framework\App\State\CleanupFiles', $cleanupFiles],
            ['Magento\Framework\App\Cache', $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->getMock('Magento\Setup\Console\Command\ModuleEnableCommand', [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $status->expects($this->atLeastOnce())->method('add');
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
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
