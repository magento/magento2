<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Console\Command\CronRunCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CronRunCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var CronRunCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\Queue
     */
    private $queue;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\ReadinessCheck
     */
    private $readinessCheck;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\Status
     */
    private $status;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->queue = $this->getMock('Magento\Setup\Model\Cron\Queue', [], [], '', false);
        $this->readinessCheck = $this->getMock('Magento\Setup\Model\Cron\ReadinessCheck', [], [], '', false);
        $this->status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $this->command = new CronRunCommand(
            $this->deploymentConfig,
            $this->queue,
            $this->readinessCheck,
            $this->status
        );
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->status->expects($this->once())->method($this->anything());
        $this->queue->expects($this->never())->method($this->anything());
        $this->readinessCheck->expects($this->never())->method($this->anything());
        $this->commandTester->execute([]);
    }

    public function testExecuteFailedReadinessCheck()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->status->expects($this->never())->method($this->anything());
        $this->queue->expects($this->never())->method($this->anything());
        $this->readinessCheck->expects($this->once())->method('runReadinessCheck')->willReturn(false);
        $this->commandTester->execute([]);
    }

    public function testExecuteUpdateInProgress()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->queue->expects($this->never())->method($this->anything());
        $this->readinessCheck->expects($this->once())->method('runReadinessCheck')->willReturn(true);
        $this->status->expects($this->once())->method('isUpdateInProgress')->willReturn(true);
        $this->status->expects($this->never())->method('add');
        $this->status->expects($this->never())->method('isUpdateError');
        $this->commandTester->execute([]);
    }

    public function testExecuteUpdateError()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->queue->expects($this->never())->method($this->anything());
        $this->readinessCheck->expects($this->once())->method('runReadinessCheck')->willReturn(true);
        $this->status->expects($this->once())->method('isUpdateInProgress')->willReturn(false);
        $this->status->expects($this->never())->method('add');
        $this->status->expects($this->once())->method('isUpdateError')->willReturn(true);
        $this->commandTester->execute([]);
    }

    public function testExecuteErrorOnToggleInProgress()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->queue->expects($this->never())->method($this->anything());
        $this->readinessCheck->expects($this->once())->method('runReadinessCheck')->willReturn(true);
        $this->status->expects($this->once())->method('isUpdateInProgress')->willReturn(false);
        $this->status->expects($this->once())->method('add')->with('runtime exception');
        $this->status->expects($this->once())->method('isUpdateError')->willReturn(false);
        $this->status->expects($this->once())
            ->method('toggleUpdateInProgress')
            ->willThrowException(new \RuntimeException('runtime exception'));
        $this->commandTester->execute([]);
    }

    public function setUpPreliminarySuccess()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->readinessCheck->expects($this->once())->method('runReadinessCheck')->willReturn(true);
        $this->status->expects($this->once())->method('isUpdateInProgress')->willReturn(false);
        $this->status->expects($this->once())->method('isUpdateError')->willReturn(false);
        $this->status->expects($this->exactly(2))->method('toggleUpdateInProgress');
    }

    public function testExecuteNoJobInQueue()
    {
        $this->setUpPreliminarySuccess();
        $this->queue->expects($this->once())->method('peek')->willReturn([]);
        $this->queue->expects($this->never())->method('popQueuedJob');
        $this->commandTester->execute([]);
    }

    public function testExecuteFirstJobNotSupported()
    {
        $this->setUpPreliminarySuccess();
        $this->queue->expects($this->exactly(2))->method('peek')->willReturn(['name' => 'update']);
        $this->queue->expects($this->never())->method('popQueuedJob');
        $this->commandTester->execute([]);
    }

    public function testExecutePopQueueFails()
    {
        $this->setUpPreliminarySuccess();
        $this->queue->expects($this->exactly(2))->method('peek')->willReturn(['name' => 'setup:']);
        $this->queue->expects($this->once())->method('popQueuedJob')->willThrowException(new \Exception('pop failed'));
        $this->status->expects($this->once())->method('add')->with('pop failed');
        $this->status->expects($this->once())->method('toggleUpdateError')->with(true);
        $this->commandTester->execute([]);
    }

    public function testExecuteJobFailed()
    {
        $this->setUpPreliminarySuccess();
        $this->queue->expects($this->at(0))->method('peek')->willReturn(['name' => 'setup:']);
        $this->queue->expects($this->at(1))->method('peek')->willReturn(['name' => 'setup:']);
        $job = $this->getMockForAbstractClass('Magento\Setup\Model\Cron\AbstractJob', [], '', false);
        $job->expects($this->once())->method('execute')->willThrowException(new \Exception('job failed'));
        $this->queue->expects($this->at(2))->method('popQueuedJob')->willReturn($job);
        $this->status->expects($this->atLeastOnce())->method('toggleUpdateError')->with(true);
        $this->commandTester->execute([]);
    }

    public function testExecute()
    {
        $this->setUpPreliminarySuccess();
        $this->queue->expects($this->at(0))->method('peek')->willReturn(['name' => 'setup:']);
        $this->queue->expects($this->at(1))->method('peek')->willReturn(['name' => 'setup:']);
        $job = $this->getMockForAbstractClass('Magento\Setup\Model\Cron\AbstractJob', [], '', false);
        $job->expects($this->once())->method('execute');
        $this->queue->expects($this->at(2))->method('popQueuedJob')->willReturn($job);
        $this->status->expects($this->never())->method('toggleUpdateError')->with(true);
        $this->commandTester->execute([]);
    }
}
