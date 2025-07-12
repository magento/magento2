<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Console\Command;

use Magento\Cron\Console\Command\CronCommand;
use Magento\Framework\App\Cron;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CronCommandTest extends TestCase
{
    /**
     * @var ObjectManagerFactory|MockObject
     */
    private $objectManagerFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    protected function setUp(): void
    {
        $this->objectManagerFactory = $this->createMock(ObjectManagerFactory::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
    }

    /**
     * Test command with disables cron
     *
     * @return void
     */
    public function testExecuteWithDisabledCrons()
    {
        $this->objectManagerFactory->expects($this->never())
            ->method('create');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('cron/enabled', 1)
            ->willReturn(0);
        $commandTester = new CommandTester(
            new CronCommand($this->objectManagerFactory, $this->deploymentConfigMock)
        );
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
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $cron = $this->createMock(Cron::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->willReturn($cron);
        $cron->expects($this->once())
            ->method('launch');
        $this->objectManagerFactory->expects($this->once())
            ->method('create')
            ->willReturn($objectManager);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('cron/enabled', 1)
            ->willReturn(1);
        $commandTester = new CommandTester(
            new CronCommand($this->objectManagerFactory, $this->deploymentConfigMock)
        );
        $commandTester->execute([]);
        $expectedMsg = '';
        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }
}
