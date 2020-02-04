<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\App\Cache;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\MaintenanceDisableCommand;
use Magento\Setup\Console\Command\MaintenanceEnableCommand;
use Magento\Setup\Model\Cron\JobSetMaintenanceMode;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class JobSetMaintenanceModeTest
 */
class JobSetMaintenanceModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Status|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusMock;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputMock;

    /**
     * @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProviderMock;

    public function setUp()
    {
        $this->objectManagerProviderMock = $this->createMock(ObjectManagerProvider::class);
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(CleanupFiles::class);
        $cache = $this->createMock(Cache::class);
        $valueMap = [
            [CleanupFiles::class, $cleanupFiles],
            [Cache::class, $cache],

        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $this->objectManagerProviderMock->expects($this->once())->method('get')->willReturn($objectManager);

        $this->statusMock = $this->createMock(Status::class);
        $this->outputMock = $this->createMock(OutputInterface::class);
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
     *
     * @expectedException \RuntimeException
     */
    public function testExecuteMaintenanceModeDisableExeption()
    {
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
