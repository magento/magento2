<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\DependencyChecker;
use Magento\Setup\Model\ThemeDependencyCheckerFactory;
use Magento\Setup\Model\UninstallDependencyCheck;
use Magento\Theme\Model\Theme\ThemeDependencyChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UninstallDependencyCheckTest extends TestCase
{
    /**
     * @var UninstallDependencyCheck
     */
    private $uninstallDependencyCheck;

    /**
     * @var ComposerInformation|MockObject
     */
    private $composerInfo;

    /**
     * @var DependencyChecker|MockObject
     */
    private $packageDependencyChecker;

    /**
     * @var ThemeDependencyChecker|MockObject
     */
    private $themeDependencyChecker;

    /**
     * @var ThemeDependencyCheckerFactory|MockObject
     */
    private $themeDependencyCheckerFactory;

    protected function setup(): void
    {
        $this->composerInfo = $this->createMock(ComposerInformation::class);
        $this->packageDependencyChecker = $this->createMock(DependencyChecker::class);
        $this->themeDependencyChecker = $this->createMock(ThemeDependencyChecker::class);
        $this->themeDependencyCheckerFactory =
            $this->createMock(ThemeDependencyCheckerFactory::class);
        $this->themeDependencyCheckerFactory->expects($this->any())->method('create')
            ->willReturn($this->themeDependencyChecker);
        $this->uninstallDependencyCheck = new UninstallDependencyCheck(
            $this->composerInfo,
            $this->packageDependencyChecker,
            $this->themeDependencyCheckerFactory
        );
    }

    public function testRunUninstallReadinessCheck()
    {
        $packages = [
            'verndor/module' => 'magento2-module',
            'verndor/theme' => 'magento2-theme',
            'verndor/metapackage' => 'metapackage',
            'verndor/language' => 'magento2-language',
        ];

        $this->composerInfo->expects($this->once())->method('getRootRequiredPackageTypesByName')->willReturn($packages);
        $this->packageDependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(array_keys($packages))
            ->willReturn([]);

        $this->themeDependencyChecker->expects($this->once())
            ->method('checkChildThemeByPackagesName')
            ->with(['verndor/theme'])
            ->willReturn([]);

        $result = $this->uninstallDependencyCheck->runUninstallReadinessCheck(array_keys($packages));
        $this->assertEquals(['success' => true], $result);
    }

    public function testRunUninstallReadinessCheckWithError()
    {
        $packages = [
            'verndor/module' => 'magento2-module',
            'verndor/theme' => 'magento2-theme',
            'verndor/metapackage' => 'metapackage',
            'verndor/language' => 'magento2-language',
        ];

        $this->composerInfo->expects($this->once())->method('getRootRequiredPackageTypesByName')->willReturn($packages);
        $this->packageDependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->with(array_keys($packages))
            ->willReturn([]);

        $this->themeDependencyChecker->expects($this->once())
            ->method('checkChildThemeByPackagesName')
            ->with(['verndor/theme'])
            ->willReturn(['Error message']);

        $result = $this->uninstallDependencyCheck->runUninstallReadinessCheck(array_keys($packages));
        $this->assertEquals(['success' => false, 'error' => 'Error message'], $result);
    }
}
