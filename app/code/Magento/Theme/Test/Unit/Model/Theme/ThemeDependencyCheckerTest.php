<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Theme\Model\Theme\ThemeDependencyChecker;

class ThemeDependencyCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeDependencyChecker
     */
    private $themeDependencyChecker;

    /**
     * @var \Magento\Theme\Model\Theme\Data\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeCollection;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeProvider;

    /**
     * @var \Magento\Theme\Model\Theme\ThemePackageInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themePackageInfo;

    public function setup()
    {
        $this->themePackageInfo = $this->getMock(\Magento\Theme\Model\Theme\ThemePackageInfo::class, [], [], '', false);
        $this->themeCollection = $this->getMock(\Magento\Theme\Model\Theme\Data\Collection::class, [], [], '', false);
        $this->themeProvider = $this->getMock(\Magento\Theme\Model\Theme\ThemeProvider::class, [], [], '', false);

        $this->themeDependencyChecker = new ThemeDependencyChecker(
            $this->themeCollection,
            $this->themeProvider,
            $this->themePackageInfo
        );
    }

    public function testCheckChildThemeByPackagesName()
    {
        $packages = [
            'vendor/package1',
            'vendor/package2'
        ];
        $this->themePackageInfo->expects($this->exactly(2))->method('getFullThemePath')->willReturn(null);
        $this->themeDependencyChecker->checkChildThemeByPackagesName($packages);
    }

    /**
     * @dataProvider executeFailedChildThemeCheckDataProvider
     * @param bool $hasVirtual
     * @param bool $hasPhysical
     * @param array $input
     * @param string $expected
     * @return void
     */
    public function testExecuteFailedChildThemeCheck($hasVirtual, $hasPhysical, array $input, $expected)
    {
        $theme = $this->getMock(\Magento\Theme\Model\Theme::class, [], [], '', false);
        $theme->expects($this->any())->method('hasChildThemes')->willReturn($hasVirtual);
        $parentThemeA = $this->getMock(\Magento\Theme\Model\Theme::class, [], [], '', false);
        $parentThemeA->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/a');
        $parentThemeB = $this->getMock(\Magento\Theme\Model\Theme::class, [], [], '', false);
        $parentThemeB->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/b');
        $childThemeC = $this->getMock(\Magento\Theme\Model\Theme::class, [], [], '', false);
        $childThemeC->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/c');
        $childThemeD = $this->getMock(\Magento\Theme\Model\Theme::class, [], [], '', false);
        $childThemeD->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/d');

        if ($hasPhysical) {
            $childThemeC->expects($this->any())->method('getParentTheme')->willReturn($parentThemeA);
            $childThemeD->expects($this->any())->method('getParentTheme')->willReturn($parentThemeB);
        }

        $this->themeProvider->expects($this->any())->method('getThemeByFullPath')->willReturn($theme);
        $this->themeCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$childThemeC, $childThemeD]));

        $this->assertEquals($expected, $this->themeDependencyChecker->checkChildTheme($input));
    }

    /**
     * @return array
     */
    public function executeFailedChildThemeCheckDataProvider()
    {
        return [
            [
                true,
                false,
                ['frontend/Magento/a'],
                ['frontend/Magento/a is a parent of virtual theme. Parent themes cannot be uninstalled.']
            ],
            [
                true,
                false,
                ['frontend/Magento/a', 'frontend/Magento/b'],
                ['frontend/Magento/a, frontend/Magento/b are parents of virtual theme.'
                . ' Parent themes cannot be uninstalled.']
            ],
            [
                false,
                true,
                ['frontend/Magento/a'],
                ['frontend/Magento/a is a parent of physical theme. Parent themes cannot be uninstalled.']
            ],
            [
                false,
                true,
                ['frontend/Magento/a', 'frontend/Magento/b'],
                ['frontend/Magento/a, frontend/Magento/b are parents of physical theme.'
                . ' Parent themes cannot be uninstalled.']
            ],
            [
                true,
                true,
                ['frontend/Magento/a'],
                ['frontend/Magento/a is a parent of virtual theme. Parent themes cannot be uninstalled.',
                'frontend/Magento/a is a parent of physical theme. Parent themes cannot be uninstalled.']
            ],
            [
                true,
                true,
                ['frontend/Magento/a', 'frontend/Magento/b'],
                ['frontend/Magento/a, frontend/Magento/b are parents of virtual theme.'
                . ' Parent themes cannot be uninstalled.',
                'frontend/Magento/a, frontend/Magento/b are parents of physical theme.'
                . ' Parent themes cannot be uninstalled.']
            ],
        ];
    }
}
