<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobUpgrade;

class JobUpgradeTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $queue = $this->getMock(\Magento\Setup\Model\Cron\Queue::class, [], [], '', false);
        $queue->expects($this->exactly(2))->method('addJobs');
        $command = $this->getMock(\Magento\Setup\Console\Command\UpgradeCommand::class, [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock(\Magento\Setup\Model\Cron\Status::class, [], [], '', false);
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $objectManagerProvider =
            $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, ['get'], [], '', false);
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
