<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobUpgrade;

class JobUpgradeTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $queue = $this->createMock(\Magento\Setup\Model\Cron\Queue::class);
        $queue->expects($this->exactly(2))->method('addJobs');
        $command = $this->createMock(\Magento\Setup\Console\Command\UpgradeCommand::class);
        $command->expects($this->once())->method('run');
        $status = $this->createMock(\Magento\Setup\Model\Cron\Status::class);
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $objectManagerProvider =
            $this->createPartialMock(\Magento\Setup\Model\ObjectManagerProvider::class, ['get']);
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
