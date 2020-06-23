<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\Composer\Remove;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\ThemePackageInfo;
use Magento\Theme\Model\Theme\ThemeProvider;
use Magento\Theme\Model\Theme\ThemeUninstaller;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeUninstallerTest extends TestCase
{
    /**
     * @var ThemePackageInfo|MockObject
     */
    private $themePackageInfo;

    /**
     * @var Remove|MockObject
     */
    private $remove;

    /**
     * @var ThemeProvider|MockObject
     */
    private $themeProvider;

    /**
     * @var ThemeUninstaller
     */
    private $themeUninstaller;

    /**
     * @var OutputInterface|MockObject
     */
    private $output;

    protected function setUp(): void
    {
        $this->themePackageInfo = $this->createMock(ThemePackageInfo::class);
        $this->remove = $this->createMock(Remove::class);
        $this->themeProvider = $this->createMock(ThemeProvider::class);
        $this->themeUninstaller = new ThemeUninstaller($this->themePackageInfo, $this->remove, $this->themeProvider);
        $this->output = $this->getMockForAbstractClass(
            OutputInterface::class,
            [],
            '',
            false
        );
    }

    public function testUninstallRegistry()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->themePackageInfo->expects($this->never())->method($this->anything());
        $this->remove->expects($this->never())->method($this->anything());
        $theme = $this->createMock(Theme::class);
        $theme->expects($this->exactly(3))->method('delete');
        $this->themeProvider->expects($this->exactly(3))->method('getThemeByFullPath')->willReturn($theme);
        $this->themeUninstaller->uninstallRegistry(
            $this->output,
            ['frontend/Magento/ThemeA', 'frontend/Magento/ThemeB', 'frontend/Magento/ThemeC']
        );
    }

    public function testUninstallCode()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->themePackageInfo->expects($this->at(0))->method('getPackageName')->willReturn('packageA');
        $this->themePackageInfo->expects($this->at(1))->method('getPackageName')->willReturn('packageB');
        $this->themePackageInfo->expects($this->at(2))->method('getPackageName')->willReturn('packageC');
        $this->remove->expects($this->once())
            ->method('remove')
            ->with(['packageA', 'packageB', 'packageC'])
            ->willReturn('');
        $this->themeProvider->expects($this->never())->method($this->anything());
        $this->themeUninstaller->uninstallCode(
            $this->output,
            ['frontend/Magento/ThemeA', 'frontend/Magento/ThemeB', 'frontend/Magento/ThemeC']
        );
    }
}
