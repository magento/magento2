<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Console\Command\CronRunCommand;
use Magento\Setup\Model\Cron\AbstractJob;
use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Cron\ReadinessCheck;
use Magento\Setup\Model\Cron\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CronRunCommandTest extends TestCase
{
    /**
     * @var MockObject|DeploymentConfig
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
     * @var MockObject|Queue
     */
    private $queue;

    /**
     * @var MockObject|ReadinessCheck
     */
    private $readinessCheck;

    /**
     * @var MockObject|Status
     */
    private $status;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->queue = $this->createMock(Queue::class);
        $this->readinessCheck = $this->createMock(ReadinessCheck::class);
        $this->status = $this->createMock(Status::class);
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
        $job = $this->getMockForAbstractClass(AbstractJob::class, [], '', false);
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
        $job = $this->getMockForAbstractClass(AbstractJob::class, [], '', false);
        $job->expects($this->once())->method('execute');
        $this->queue->expects($this->at(2))->method('popQueuedJob')->willReturn($job);
        $this->status->expects($this->never())->method('toggleUpdateError')->with(true);
        $this->commandTester->execute([]);
    }
}
