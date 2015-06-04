<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Test\Unit\Console\Command;

use Magento\Framework\Composer\GeneralDependencyChecker;
use Magento\Framework\Composer\Remove;
use Magento\Framework\Composer\ComposerInformation;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Translation\Console\Command\UninstallLanguageCommand;

class UninstallLanguageCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GeneralDependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dependencyChecker;

    /**
     * @var Remove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $remove;

    /**
     * @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInfo;

    /**
     * @var UninstallLanguageCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $this->dependencyChecker = $this->getMock(
            'Magento\Framework\Composer\GeneralDependencyChecker',
            [],
            [],
            '',
            false
        );
        $this->remove = $this->getMock('Magento\Framework\Composer\Remove', [], [], '', false);
        $this->composerInfo = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);

        $this->command = new UninstallLanguageCommand($this->dependencyChecker, $this->remove, $this->composerInfo);

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
            ->method('getRootRequiredPackagesAndTypes')
            ->willReturn(
                [
                    'vendor/language-ua_ua' => 'magento2-language'
                ]
            );

        $this->remove->expects($this->once())->method('remove');

        $this->tester->execute(['package' => ['vendor/language-ua_ua']]);
    }

    public function testExecutePackageHasDependency()
    {
        $dependencies['vendor/language-ua_ua'] = ['some/dependency'];

        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(['vendor/language-ua_ua'])
            ->willReturn($dependencies);

        $this->composerInfo->expects($this->once())
            ->method('getRootRequiredPackagesAndTypes')
            ->willReturn(
                [
                    'vendor/language-ua_ua' => 'magento2-language'
                ]
            );

        $this->remove->expects($this->never())->method('remove');

        $this->tester->execute(['package' => ['vendor/language-ua_ua']]);
        $this->assertContains(
            'Package vendor/language-ua_ua has dependencies and will be skipped',
            $this->tester->getDisplay()
        );
        $this->assertContains('Nothing is removed.', $this->tester->getDisplay());
    }

    public function testExecutePackageNoLanguage()
    {
        $dependencies['vendor/language-ua_ua'] = [];

        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(['vendor/language-ua_ua'])
            ->willReturn($dependencies);

        $this->composerInfo->expects($this->once())
            ->method('getRootRequiredPackagesAndTypes')
            ->willReturn(
                [
                    'vendor/language-ua_ua' => 'library'
                ]
            );

        $this->remove->expects($this->never())->method('remove');

        $this->tester->execute(['package' => ['vendor/language-ua_ua']]);
        $this->assertContains(
            'Package vendor/language-ua_ua is not magento language and will be skipped',
            $this->tester->getDisplay()
        );
        $this->assertContains('Nothing is removed.', $this->tester->getDisplay());
    }
}
