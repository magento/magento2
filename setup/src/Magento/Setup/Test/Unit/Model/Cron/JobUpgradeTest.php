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
        $queue = $this->getMock('Magento\Setup\Model\Cron\Queue', [], [], '', false);
        $command = $this->getMock('Magento\Setup\Console\Command\UpgradeCommand', [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
        $jobUpgrade = new JobUpgrade(
            $command,
            $output,
            $queue,
            $status,
            'setup:upgrade',
            []
        );
        $jobUpgrade->execute();
    }
}
