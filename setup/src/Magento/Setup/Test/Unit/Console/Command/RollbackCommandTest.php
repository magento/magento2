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
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $path = realpath(__DIR__ . '/../../_files/');
        $this->directoryList->expects($this->any())
            ->method('getRoot')
            ->willReturn($path);
        $this->directoryList->expects($this->any())
            ->method('getPath')
            ->willReturn($path);
        $command = new RollbackCommand(
            $objectManagerProvider,
            $maintenanceMode,
            $this->directoryList,
            $this->deploymentConfig
        );
        $this->tester = new CommandTester($command);
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
        $helper = $this->getMock('Magento\Framework\Backup\Filesystem\Helper', [], [], '', false);
        $helper->expects($this->once())
            ->method('getInfo')
            ->willReturn(['writable' => true]);
        $filesystem = $this->getMock('Magento\Framework\Backup\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('addIgnorePaths');
        $filesystem->expects($this->once())
            ->method('setBackupsDir');
        $filesystem->expects($this->once())
            ->method('setBackupExtension');
        $filesystem->expects($this->once())
            ->method('setTime');
        $filesystem->expects($this->once())
            ->method('rollback');
        $filesystem->expects($this->once())
            ->method('getBackupFilename')
            ->willReturn('RollbackFile_A.tgz');
        $filesystem->expects($this->once())
            ->method('getBackupPath')
            ->willReturn('pathToFile/RollbackFile_A.tgz');
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Backup\Filesystem\Helper', [], $helper],
                ['Magento\Framework\Backup\Filesystem', [], $filesystem],
            ]));
        $this->tester->execute(['--code' => 'RollbackFile_A.tgz']);
        $expectedMsg = 'Enabling maintenance mode' . PHP_EOL
            . 'Code rollback filename: RollbackFile_A.tgz' . PHP_EOL
            . 'Code rollback file path: pathToFile/RollbackFile_A.tgz' . PHP_EOL
            . '[SUCCESS]: Code rollback is completed successfully.' . PHP_EOL .'Disabling maintenance mode' . PHP_EOL;
        $this->assertEquals($expectedMsg, $this->tester->getDisplay());
    }
}
