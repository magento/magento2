<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\RollbackCommand;
use Symfony\Component\Console\Tester\CommandTester;

class RollbackCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Setup\Model\BackupRollback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollback;

    /**
     * @var RollbackCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false)
        ;
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->backupRollback = $this->getMock('Magento\Setup\Model\BackupRollback', [], [], '', false);
        $this->command = new RollbackCommand(
            $objectManagerProvider,
            $maintenanceMode,
            $this->backupRollback,
            $this->deploymentConfig
        );
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteApplicationNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->tester->execute(['--code' => ['RollbackFile_A']]);
        $this->assertEquals(
            'You cannot run this command because the Magento application is not installed.' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->backupRollback->expects($this->once())
            ->method('codeRollback')
            ->with($this->objectManager, $this->isInstanceOf('Magento\Setup\Model\ConsoleLogger'));
        $this->tester->execute(['--code' => ['RollbackFile_A']]);
    }
}
