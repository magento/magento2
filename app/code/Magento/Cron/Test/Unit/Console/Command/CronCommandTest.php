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
    /**
     * Test command with disables cron
     *
     * @return void
     */
    public function testExecuteWithDisabledCrons()
    {
        $objectManagerFactory = $this->createMock(\Magento\Framework\App\ObjectManagerFactory::class);
        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

        $objectManagerFactory->expects($this->never())
            ->method('create');
        $deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('cron/enabled', 1)
            ->willReturn(0);
        $commandTester = new CommandTester(new CronCommand($objectManagerFactory, $deploymentConfigMock));
        $commandTester->execute([]);
        $expectedMsg = 'Cron is disabled. Jobs were not run.' . PHP_EOL;
        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * Test command with enabled cron
     *
     * @return void
     */
    public function testExecute()
    {
        $objectManagerFactory = $this->createMock(\Magento\Framework\App\ObjectManagerFactory::class);
        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $cron = $this->createMock(\Magento\Framework\App\Cron::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->willReturn($cron);
        $cron->expects($this->once())
            ->method('launch');
        $objectManagerFactory->expects($this->once())
            ->method('create')
            ->willReturn($objectManager);
        $deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('cron/enabled', 1)
            ->willReturn(1);
        $commandTester = new CommandTester(new CronCommand($objectManagerFactory, $deploymentConfigMock));
        $commandTester->execute([]);
        $expectedMsg = 'Ran jobs by schedule.' . PHP_EOL;
        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }
}
