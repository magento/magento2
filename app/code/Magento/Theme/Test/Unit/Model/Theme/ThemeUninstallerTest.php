<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Theme\Model\Theme\ThemeUninstaller;

class ThemeUninstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Theme\PackageNameFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageNameFinder;

    /**
     * @var \Magento\Framework\Composer\Remove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $remove;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeProvider;

    /**
     * @var ThemeUninstaller
     */
    private $themeUninstaller;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $output;

    public function setUp()
    {
        $this->packageNameFinder = $this->getMock('Magento\Theme\Model\Theme\PackageNameFinder', [], [], '', false);
        $this->remove = $this->getMock('Magento\Framework\Composer\Remove', [], [], '', false);
        $this->themeProvider = $this->getMock('Magento\Theme\Model\Theme\ThemeProvider', [], [], '', false);
        $this->themeUninstaller = new ThemeUninstaller($this->packageNameFinder, $this->remove, $this->themeProvider);
        $this->output = $this->getMockForAbstractClass(
            'Symfony\Component\Console\Output\OutputInterface',
            [],
            '',
            false
        );
    }

    public function testUninstallRegistry()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->packageNameFinder->expects($this->never())->method($this->anything());
        $this->remove->expects($this->never())->method($this->anything());
        $theme = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $theme->expects($this->exactly(3))->method('delete');
        $this->themeProvider->expects($this->exactly(3))->method('getThemeByFullPath')->willReturn($theme);
        $this->themeUninstaller->uninstall(
            $this->output,
            ['frontend/Magento/ThemeA', 'frontend/Magento/ThemeB', 'frontend/Magento/ThemeC'],
            [ThemeUninstaller::OPTION_UNINSTALL_REGISTRY => true]
        );
    }

    public function testUninstallCode()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->packageNameFinder->expects($this->at(0))->method('getPackageName')->willReturn('packageA');
        $this->packageNameFinder->expects($this->at(1))->method('getPackageName')->willReturn('packageB');
        $this->packageNameFinder->expects($this->at(2))->method('getPackageName')->willReturn('packageC');
        $this->remove->expects($this->once())
            ->method('remove')
            ->with(['packageA', 'packageB', 'packageC'])
            ->willReturn('');
        $this->themeProvider->expects($this->never())->method($this->anything());
        $this->themeUninstaller->uninstall(
            $this->output,
            ['frontend/Magento/ThemeA', 'frontend/Magento/ThemeB', 'frontend/Magento/ThemeC'],
            [ThemeUninstaller::OPTION_UNINSTALL_CODE => true]
        );
    }
}
