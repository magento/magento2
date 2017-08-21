<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Console\Command;

use Magento\Cron\Console\Command\CronCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CronCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $objectManagerFactory = $this->createMock(\Magento\Framework\App\ObjectManagerFactory::class);
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $cron = $this->createMock(\Magento\Framework\App\Cron::class);
        $objectManager->expects($this->once())->method('create')->willReturn($cron);
        $cron->expects($this->once())->method('launch');
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
        $commandTester = new CommandTester(new CronCommand($objectManagerFactory));
        $commandTester->execute([]);
        $expectedMsg = 'Ran jobs by schedule.' . PHP_EOL;
        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }
}
