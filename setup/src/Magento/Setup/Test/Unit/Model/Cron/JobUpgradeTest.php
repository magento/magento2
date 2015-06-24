<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobUpgrade;

class JobUpgradeTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles');
        $cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $cache->expects($this->once())->method('clean');
        $valueMap = [
            ['Magento\Framework\App\State\CleanupFiles', $cleanupFiles],
            ['Magento\Framework\App\Cache', $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $command = $this->getMock('Magento\Setup\Console\Command\UpgradeCommand', [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $status->expects($this->atLeastOnce())->method('add');
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $maintenanceMode->expects($this->once())->method('set')->with(false);
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
        $jobUpgrade = new JobUpgrade(
            $command,
            $objectManagerProvider,
            $maintenanceMode,
            $output,
            $status,
            'setup:upgrade',
            []
        );
        $jobUpgrade->execute();
    }
}
