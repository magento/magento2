<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Console\Command\RollbackCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RollbackCommandTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var BackupRollback|MockObject
     */
    private $backupRollback;

    /**
     * @var BackupRollbackFactory|MockObject
     */
    private $backupRollbackFactory;

    /**
     * @var HelperSet|MockObject
     */
    private $helperSet;

    /**
     * @var QuestionHelper|MockObject
     */
    private $question;

    /**
     * @var RollbackCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $maintenanceMode = $this->createMock(MaintenanceMode::class);
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->backupRollback = $this->createMock(BackupRollback::class);
        $this->backupRollbackFactory = $this->createMock(BackupRollbackFactory::class);
        $this->backupRollbackFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->backupRollback);
        $appState = $this->createMock(State::class);
        $configLoader = $this->getMockForAbstractClass(
            ConfigLoaderInterface::class,
            [],
            '',
            false
        );
        $configLoader->expects($this->any())->method('load')->willReturn([]);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [BackupRollbackFactory::class, $this->backupRollbackFactory],
                [State::class, $appState],
                [ConfigLoaderInterface::class, $configLoader],
            ]);
        $this->helperSet = $this->createMock(HelperSet::class);
        $this->question = $this->createMock(QuestionHelper::class);
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
