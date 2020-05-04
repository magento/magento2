<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\App\Cache;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\ModuleDisableCommand;
use Magento\Setup\Console\Command\ModuleEnableCommand;
use Magento\Setup\Model\Cron\JobModule;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class JobModuleTest extends TestCase
{
    public function testExecuteModuleDisable()
    {
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManager =
            $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(CleanupFiles::class);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->createMock(Cache::class);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            [PackageInfoFactory::class],
            [CleanupFiles::class, $cleanupFiles],
            [Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->willReturnMap($valueMap);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->createMock(ModuleDisableCommand::class);
        $command->expects($this->once())->method('run');
        $status = $this->createMock(Status::class);
        $status->expects($this->atLeastOnce())->method('add');
        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
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
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManager =
            $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(CleanupFiles::class);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->createMock(Cache::class);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            [PackageInfoFactory::class],
            [CleanupFiles::class, $cleanupFiles],
            [Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->willReturnMap($valueMap);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->createMock(ModuleEnableCommand::class);
        $command->expects($this->once())->method('run');
        $status = $this->createMock(Status::class);
        $status->expects($this->atLeastOnce())->method('add');
        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
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
