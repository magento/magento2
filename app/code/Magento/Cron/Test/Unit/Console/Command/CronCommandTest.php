<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Console\Command;

use Magento\Cron\Console\Command\CronCommand;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\ObjectManagerFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Tester\CommandTester;

class CronCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerFactory|MockObject
     */
    private $objectManagerFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var MaintenanceMode|MockObject
     */
    private $maintenanceModeMock;

    protected function setUp()
    {
        $this->objectManagerFactory = $this->createMock(ObjectManagerFactory::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->maintenanceModeMock = $this->createMock(MaintenanceMode::class);
    }

    /**
     * Test command with disables cron
     *
     * @return void
     */
    public function testExecuteWithDisabledCrons()
    {
        $this->maintenanceModeMock->expects($this->once())
            ->method('isOn')
            ->willReturn(false);
        $this->objectManagerFactory->expects($this->never())
            ->method('create');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('cron/enabled', 1)
            ->willReturn(0);
        $commandTester = new CommandTester(
            new CronCommand($this->objectManagerFactory, $this->deploymentConfigMock, $this->maintenanceModeMock)
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
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $cron = $this->createMock(\Magento\Framework\App\Cron::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->willReturn($cron);
        $cron->expects($this->once())
            ->method('launch');
        $this->maintenanceModeMock->expects($this->once())
            ->method('isOn')
            ->willReturn(false);
        $this->objectManagerFactory->expects($this->once())
            ->method('create')
            ->willReturn($objectManager);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('cron/enabled', 1)
            ->willReturn(1);
        $commandTester = new CommandTester(
            new CronCommand($this->objectManagerFactory, $this->deploymentConfigMock, $this->maintenanceModeMock)
        );
        $commandTester->execute([]);
        $expectedMsg = 'Ran jobs by schedule.' . PHP_EOL;
        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * Test command with maintenance mode enabled
     *
     * @return void
     */
    public function testWithDisabledMaintenanceMode()
    {
        $this->maintenanceModeMock->expects($this->once())
            ->method('isOn')
            ->willReturn(true);
        $this->objectManagerFactory->expects($this->never())
            ->method('create');
        $this->deploymentConfigMock->expects($this->never())
            ->method('get');
        $commandTester = new CommandTester(
            new CronCommand($this->objectManagerFactory, $this->deploymentConfigMock, $this->maintenanceModeMock)
        );
        $commandTester->execute([]);
        $expectedMsg = 'Cron is disabled because the maintenance mode is enabled.' . PHP_EOL;
        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }
}
