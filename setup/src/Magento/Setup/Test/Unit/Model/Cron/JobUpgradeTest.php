<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobUpgrade;

class JobUpgradeTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $queue = $this->getMock('Magento\Setup\Model\Cron\Queue', [], [], '', false);
        $queue->expects($this->exactly(2))->method('addJobs');
        $command = $this->getMock('Magento\Setup\Console\Command\UpgradeCommand', [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', ['get'], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $jobUpgrade = new JobUpgrade(
            $command,
            $objectManagerProvider,
            $output,
            $queue,
            $status,
            'setup:upgrade',
            []
        );
        $jobUpgrade->execute();
    }
}
