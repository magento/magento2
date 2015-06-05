<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\BackupCommand;
use Symfony\Component\Console\Tester\CommandTester;

class BackupCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Setup\BackupRollback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollback;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Framework\Setup\BackupRollbackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollbackFactory;

    public function setUp()
    {
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->backupRollback = $this->getMock('Magento\Framework\Setup\BackupRollback', [], [], '', false);
        $this->backupRollbackFactory = $this->getMock(
            'Magento\Framework\Setup\BackupRollbackFactory',
            [],
            [],
            '',
            false
        );
        $this->backupRollbackFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->backupRollback);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\Setup\BackupRollbackFactory', $this->backupRollbackFactory],
            ]));
        $command = new BackupCommand(
            $objectManagerProvider,
            $maintenanceMode
        );
        $this->tester = new CommandTester($command);
    }

    public function testExecuteCodeBackup()
    {
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--code' => true]);
    }

    public function testExecuteMediaBackup()
    {
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--media' => true]);
    }

    public function testExecuteDBBackup()
    {
        $this->backupRollback->expects($this->once())
            ->method('dbBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--db' => true]);
    }
}
