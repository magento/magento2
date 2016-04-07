<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\UninstallDependencyCheck;

class UninstallDependencyCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UninstallDependencyCheck
     */
    private $uninstallDependencyCheck;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInfo;

    /**
     * @var \Magento\Framework\Composer\DependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageDependencyChecker;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeDependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeDependencyChecker;

    /**
     * @var \Magento\Setup\Model\ThemeDependencyCheckerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeDependencyCheckerFactory;

    public function setup()
    {
        $this->composerInfo = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $this->packageDependencyChecker = $this->getMock(
            'Magento\Framework\Composer\DependencyChecker',
            [],
            [],
            '',
            false
        );
        $this->themeDependencyChecker = $this->getMock(
            'Magento\Theme\Model\Theme\ThemeDependencyChecker',
            [],
            [],
            '',
            false
        );
        $this->themeDependencyCheckerFactory = $this->getMock(
            'Magento\Setup\Model\ThemeDependencyCheckerFactory',
            [],
            [],
            '',
            false
        );
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
