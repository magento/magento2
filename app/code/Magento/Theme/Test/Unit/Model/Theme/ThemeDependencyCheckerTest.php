<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Data\Collection;
use Magento\Theme\Model\Theme\ThemeDependencyChecker;
use Magento\Theme\Model\Theme\ThemePackageInfo;
use Magento\Theme\Model\Theme\ThemeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeDependencyCheckerTest extends TestCase
{
    /**
     * @var ThemeDependencyChecker
     */
    private $themeDependencyChecker;

    /**
     * @var Collection|MockObject
     */
    private $themeCollection;

    /**
     * @var ThemeProvider|MockObject
     */
    private $themeProvider;

    /**
     * @var ThemePackageInfo|MockObject
     */
    private $themePackageInfo;

    protected function setup(): void
    {
        $this->themePackageInfo = $this->createMock(ThemePackageInfo::class);
        $this->themeCollection = $this->createMock(Collection::class);
        $this->themeProvider = $this->createMock(ThemeProvider::class);

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
        $theme = $this->createMock(Theme::class);
        $theme->expects($this->any())->method('hasChildThemes')->willReturn($hasVirtual);
        $parentThemeA = $this->createMock(Theme::class);
        $parentThemeA->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/a');
        $parentThemeB = $this->createMock(Theme::class);
        $parentThemeB->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/b');
        $childThemeC = $this->createMock(Theme::class);
        $childThemeC->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/c');
        $childThemeD = $this->createMock(Theme::class);
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
    public static function executeFailedChildThemeCheckDataProvider()
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
