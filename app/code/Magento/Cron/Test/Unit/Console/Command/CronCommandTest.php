<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Console\Command;

use Magento\Cron\Console\Command\CronCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CronCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $cron = $this->getMock('Magento\Framework\App\Cron', [], [], '', false);
        $objectManager->expects($this->once())->method('create')->willReturn($cron);
        $cron->expects($this->once())->method('launch');
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
        $commandTester = new CommandTester(new CronCommand($objectManagerFactory));
        $commandTester->execute([]);
        $expectedMsg = 'Ran jobs by schedule.' . PHP_EOL;
        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }
}
