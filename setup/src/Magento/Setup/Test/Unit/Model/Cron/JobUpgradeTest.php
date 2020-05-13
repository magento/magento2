<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\UpgradeCommand;
use Magento\Setup\Model\Cron\JobUpgrade;
use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class JobUpgradeTest extends TestCase
{
    public function testExecute()
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->exactly(2))->method('addJobs');
        $command = $this->createMock(UpgradeCommand::class);
        $command->expects($this->once())->method('run');
        $status = $this->createMock(Status::class);
        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
        $objectManager =
            $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $objectManagerProvider =
            $this->createPartialMock(ObjectManagerProvider::class, ['get']);
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
