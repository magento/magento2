<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Setup\Console\Command\RollbackCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RollbackCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManager;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\Setup\BackupRollback|\PHPUnit\Framework\MockObject\MockObject
     */
    private $backupRollback;

    /**
     * @var \Magento\Framework\Setup\BackupRollbackFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $backupRollbackFactory;

    /**
     * @var \Symfony\Component\Console\Helper\HelperSet|\PHPUnit\Framework\MockObject\MockObject
     */
    private $helperSet;

    /**
     * @var \Symfony\Component\Console\Helper\QuestionHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $question;

    /**
     * @var RollbackCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $maintenanceMode = $this->createMock(\Magento\Framework\App\MaintenanceMode::class);
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->backupRollback = $this->createMock(\Magento\Framework\Setup\BackupRollback::class);
        $this->backupRollbackFactory = $this->createMock(\Magento\Framework\Setup\BackupRollbackFactory::class);
        $this->backupRollbackFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->backupRollback);
        $appState = $this->createMock(\Magento\Framework\App\State::class);
        $configLoader = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManager\ConfigLoaderInterface::class,
            [],
            '',
            false
        );
        $configLoader->expects($this->any())->method('load')->willReturn([]);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [\Magento\Framework\Setup\BackupRollbackFactory::class, $this->backupRollbackFactory],
                [\Magento\Framework\App\State::class, $appState],
                [\Magento\Framework\ObjectManager\ConfigLoaderInterface::class, $configLoader],
            ]);
        $this->helperSet = $this->createMock(\Symfony\Component\Console\Helper\HelperSet::class);
        $this->question = $this->createMock(\Symfony\Component\Console\Helper\QuestionHelper::class);
        $this->question
            ->expects($this->any())
            ->method('ask')
            ->willReturn(true);
        $this->helperSet
            ->expects($this->any())
            ->method('get')
            ->with('question')
            ->willReturn($this->question);
        $this->command = new RollbackCommand(
            $objectManagerProvider,
            $maintenanceMode,
            $this->deploymentConfig,
            new MaintenanceModeEnabler($maintenanceMode)
        );
        $this->command->setHelperSet($this->helperSet);
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteCodeRollback()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->backupRollback->expects($this->once())
            ->method('codeRollback')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--code-file' => 'A.tgz']);
    }

    public function testExecuteMediaRollback()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->backupRollback->expects($this->once())
            ->method('codeRollback')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--media-file' => 'A.tgz']);
    }

    public function testExecuteDBRollback()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->backupRollback->expects($this->once())
            ->method('dbRollback')
            ->willReturn($this->backupRollback);
        $this->tester->execute(['--db-file' => 'C.gz']);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
        $this->tester->execute(['--db-file' => 'C.gz']);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $this->tester->getDisplay()
        );
    }

    public function testExecuteNoOptions()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->tester->execute([]);
        $expected = 'Enabling maintenance mode' . PHP_EOL
            . 'Not enough information provided to roll back.' . PHP_EOL
            . 'Disabling maintenance mode' . PHP_EOL;
        $this->assertSame($expected, $this->tester->getDisplay());
    }

    public function testInteraction()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->question
            ->expects($this->atLeast(2))
            ->method('ask')
            ->willReturn(false);
        $this->helperSet
            ->expects($this->once())
            ->method('get')
            ->with('question')
            ->willReturn($this->question);
        $this->command->setHelperSet($this->helperSet);
        $this->tester = new CommandTester($this->command);
        $this->tester->execute(['--db-file' => 'C.gz']);
    }
}
