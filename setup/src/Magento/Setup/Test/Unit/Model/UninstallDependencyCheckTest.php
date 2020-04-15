<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\UninstallDependencyCheck;

class UninstallDependencyCheckTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UninstallDependencyCheck
     */
    private $uninstallDependencyCheck;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation|\PHPUnit\Framework\MockObject\MockObject
     */
    private $composerInfo;

    /**
     * @var \Magento\Framework\Composer\DependencyChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $packageDependencyChecker;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeDependencyChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeDependencyChecker;

    /**
     * @var \Magento\Setup\Model\ThemeDependencyCheckerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeDependencyCheckerFactory;

    protected function setup(): void
    {
        $this->composerInfo = $this->createMock(\Magento\Framework\Composer\ComposerInformation::class);
        $this->packageDependencyChecker = $this->createMock(\Magento\Framework\Composer\DependencyChecker::class);
        $this->themeDependencyChecker = $this->createMock(\Magento\Theme\Model\Theme\ThemeDependencyChecker::class);
        $this->themeDependencyCheckerFactory =
            $this->createMock(\Magento\Setup\Model\ThemeDependencyCheckerFactory::class);
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
