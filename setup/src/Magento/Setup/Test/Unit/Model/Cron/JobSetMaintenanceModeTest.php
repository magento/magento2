<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\App\Cache;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\MaintenanceDisableCommand;
use Magento\Setup\Console\Command\MaintenanceEnableCommand;
use Magento\Setup\Model\Cron\JobSetMaintenanceMode;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class JobSetMaintenanceModeTest extends TestCase
{
    /**
     * @var Status|MockObject
     */
    private $statusMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    /**
     * @var ObjectManagerProvider|MockObject
     */
    private $objectManagerProviderMock;

    protected function setUp(): void
    {
        $this->objectManagerProviderMock = $this->createMock(ObjectManagerProvider::class);
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(CleanupFiles::class);
        $cache = $this->createMock(Cache::class);
        $valueMap = [
            [CleanupFiles::class, $cleanupFiles],
            [Cache::class, $cache],

        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->willReturnMap($valueMap);
        $this->objectManagerProviderMock->expects($this->once())->method('get')->willReturn($objectManager);

        $this->statusMock = $this->createMock(Status::class);
        $this->outputMock = $this->getMockForAbstractClass(OutputInterface::class);
    }

    public function testExecuteMaintenanceModeDisable()
    {
        $command = $this->createMock(MaintenanceDisableCommand::class);
        $command->expects($this->once())->method('run');

        $jobMaintenanceDisable = new JobSetMaintenanceMode(
            $command,
            $this->objectManagerProviderMock,
            $this->outputMock,
            $this->statusMock,
            'setup:maintenance:disable'
        );
        $jobMaintenanceDisable->execute();
    }

    /**
     * Test MaintenanceModeDisable job execution when maintenance mode is set manually by admin
     */
    public function testExecuteMaintenanceModeDisableExeption()
    {
        $this->expectException('RuntimeException');
        $command = $this->createMock(MaintenanceDisableCommand::class);
        $command->expects($this->once())->method('isSetAddressInfo')->willReturn(true);
        $command->expects($this->never())->method('run');

        $jobMaintenanceDisable = new JobSetMaintenanceMode(
            $command,
            $this->objectManagerProviderMock,
            $this->outputMock,
            $this->statusMock,
            'setup:maintenance:disable'
        );
        $jobMaintenanceDisable->execute();
    }

    public function testExecuteMaintenanceModeEnable()
    {
        $command = $this->createMock(MaintenanceEnableCommand::class);
        $command->expects($this->once())->method('run');

        $jobMaintenanceEnable = new JobSetMaintenanceMode(
            $command,
            $this->objectManagerProviderMock,
            $this->outputMock,
            $this->statusMock,
            'setup:maintenance:enable'
        );
        $jobMaintenanceEnable->execute();
    }
}
