<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Console\Command;

use Magento\Framework\App\Cache;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\DependencyChecker;
use Magento\Framework\Composer\Remove;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Translation\Console\Command\UninstallLanguageCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UninstallLanguageCommandTest extends TestCase
{
    /**
     * @var DependencyChecker|MockObject
     */
    private $dependencyChecker;

    /**
     * @var Remove|MockObject
     */
    private $remove;

    /**
     * @var ComposerInformation|MockObject
     */
    private $composerInfo;

    /**
     * @var Cache|MockObject
     */
    private $cache;

    /**
     * @var BackupRollbackFactory|MockObject
     */
    private $backupRollbackFactory;

    /**
     * @var UninstallLanguageCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    protected function setUp(): void
    {
        $this->dependencyChecker = $this->createMock(DependencyChecker::class);
        $this->remove = $this->createMock(Remove::class);
        $this->composerInfo = $this->createMock(ComposerInformation::class);
        $this->cache = $this->createMock(Cache::class);
        $this->backupRollbackFactory = $this->createMock(BackupRollbackFactory::class);

        $this->command = new UninstallLanguageCommand(
            $this->dependencyChecker,
            $this->remove,
            $this->composerInfo,
            $this->cache,
            $this->backupRollbackFactory
        );

        $this->tester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $dependencies['vendor/language-ua_ua'] = [];

        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(['vendor/language-ua_ua'])
            ->willReturn($dependencies);

        $this->composerInfo->expects($this->once())
            ->method('getRootRequiredPackageTypesByName')
            ->willReturn(
                [
                    'vendor/language-ua_ua' => 'magento2-language'
                ]
            );

        $backupRollback = $this->createMock(BackupRollback::class);
        $backupRollback->expects($this->once())->method('codeBackup');

        $this->backupRollbackFactory->expects($this->once())
            ->method('create')
            ->willReturn($backupRollback);

        $this->remove->expects($this->once())->method('remove')
            ->willReturn('vendor/language-ua_ua');
        $this->cache->expects($this->once())->method('clean');

        $this->tester->execute(['package' => ['vendor/language-ua_ua'], '--backup-code' => true]);
    }

    public function testExecuteNoBackupOption()
    {
        $dependencies['vendor/language-ua_ua'] = [];

        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(['vendor/language-ua_ua'])
            ->willReturn($dependencies);

        $this->composerInfo->expects($this->once())
            ->method('getRootRequiredPackageTypesByName')
            ->willReturn(
                [
                    'vendor/language-ua_ua' => 'magento2-language'
                ]
            );

        $this->backupRollbackFactory->expects($this->never())->method('create');
        $this->remove->expects($this->once())->method('remove')
            ->willReturn('vendor/language-ua_ua');
        $this->cache->expects($this->once())->method('clean');

        $this->tester->execute(['package' => ['vendor/language-ua_ua']]);
        $this->assertStringContainsString(
            'You are removing language package without a code backup.',
            $this->tester->getDisplay()
        );
    }

    public function testExecutePackageHasDependency()
    {
        $dependencies['vendor/language-ua_ua'] = ['some/dependency'];

        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(['vendor/language-ua_ua'])
            ->willReturn($dependencies);

        $this->composerInfo->expects($this->once())
            ->method('getRootRequiredPackageTypesByName')
            ->willReturn(
                [
                    'vendor/language-ua_ua' => 'magento2-language'
                ]
            );

        $this->remove->expects($this->never())->method('remove');
        $this->cache->expects($this->never())->method('clean');

        $this->tester->execute(['package' => ['vendor/language-ua_ua']]);
        $this->assertStringContainsString(
            'Package vendor/language-ua_ua has dependencies and will be skipped',
            $this->tester->getDisplay()
        );
        $this->assertStringContainsString('Nothing is removed.', $this->tester->getDisplay());
    }

    public function testExecutePackageNoLanguage()
    {
        $dependencies['vendor/language-ua_ua'] = [];

        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(['vendor/language-ua_ua'])
            ->willReturn($dependencies);

        $this->composerInfo->expects($this->once())
            ->method('getRootRequiredPackageTypesByName')
            ->willReturn(
                [
                    'vendor/language-ua_ua' => 'library'
                ]
            );

        $this->remove->expects($this->never())->method('remove');
        $this->cache->expects($this->never())->method('clean');

        $this->tester->execute(['package' => ['vendor/language-ua_ua']]);
        $this->assertStringContainsString(
            'Package vendor/language-ua_ua is not a Magento language and will be skipped',
            $this->tester->getDisplay()
        );
        $this->assertStringContainsString('Nothing is removed.', $this->tester->getDisplay());
    }
}
