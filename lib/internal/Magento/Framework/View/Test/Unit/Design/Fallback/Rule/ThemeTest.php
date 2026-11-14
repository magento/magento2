<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Design\Fallback\Rule;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\Rule\Theme;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme as ThemeModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    /**
     * @var RuleInterface|MockObject
     */
    private $ruleMock;

    /**
     * @var ComponentRegistrarInterface|MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var DirectoryList|MockObject
     */
    private $directoryListMock;

    /**
     * @var Theme
     */
    private $model;

    protected function setUp(): void
    {
        $this->ruleMock = $this->createMock(RuleInterface::class);
        $this->componentRegistrarMock = $this->createMock(ComponentRegistrarInterface::class);
        $this->directoryListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Theme($this->ruleMock, $this->componentRegistrarMock);
        (new ObjectManager($this))->setBackwardCompatibleProperty(
            $this->model,
            'directoryList',
            $this->directoryListMock
        );
    }

    public function testGetPatternDirsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Parameter "theme" should be specified and should implement the theme interface');
        $this->model->getPatternDirs([]);
    }

    public function testGetPatternDirs()
    {
        $parentTheme = $this->createMock(ThemeInterface::class);
        $parentTheme->expects($this->exactly(2))->method('getFullPath')->willReturn('package/parent_theme');
        $parentTheme->expects($this->never())->method('getArea');
        $parentTheme->expects($this->never())->method('getCode');

        $theme = $this->createMock(ThemeInterface::class);
        $theme->expects($this->exactly(2))->method('getFullPath')->willReturn('package/current_theme');
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);
        $theme->expects($this->once())->method('getArea')->willReturn('frontend');
        $theme->expects($this->once())->method('getCode')->willReturn('luma');

        $this->componentRegistrarMock->expects($this->atLeastOnce())
            ->method('getPath')
            ->willReturnMap([
                [ComponentRegistrar::THEME, 'package/parent_theme', '/path/to/parent/theme'],
                [ComponentRegistrar::THEME, 'package/current_theme', '/path/to/current/theme'],
            ]);

        $this->directoryListMock->expects($this->atLeastOnce())
            ->method('getPath')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn('/pub/static');

        $ruleDirsMap = [
            [
                [
                    'file' => 'test.css',
                    'theme_dir' => '/path/to/current/theme',
                    'theme_pubstatic_dir' => '/pub/static/frontend/luma'
                ],
                ['package/current_theme/path/one', 'package/current_theme/path/two'],
            ],
            [
                [
                    'file' => 'test.css',
                    'theme_dir' => '/path/to/parent/theme',
                    'theme_pubstatic_dir' => '/pub/static/frontend/luma'
                ],
                ['package/parent_theme/path/one', 'package/parent_theme/path/two']
            ],
        ];
        $this->ruleMock->expects($this->atLeastOnce())->method('getPatternDirs')->willReturnMap($ruleDirsMap);
        $expectedResult = [
            'package/current_theme/path/one',
            'package/current_theme/path/two',
            'package/parent_theme/path/one',
            'package/parent_theme/path/two',
        ];
        $this->assertEquals($expectedResult, $this->model->getPatternDirs(['theme' => $theme, 'file' => 'test.css']));
    }

    public function testGetPatternDirsUsesRegisteredFullPath()
    {
        $theme = $this->createThemeModelMock('frontend/Magento/luma', 'frontend', 'Magento/luma');
        $theme->expects($this->never())->method('getArea');
        $theme->expects($this->once())->method('getParentTheme')->willReturn(null);

        $this->componentRegistrarMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, 'frontend/Magento/luma')
            ->willReturn('/path/to/frontend/luma');

        $this->ruleMock->expects($this->once())
            ->method('getPatternDirs')
            ->with(['theme_dir' => '/path/to/frontend/luma'])
            ->willReturn(['/path/to/frontend/luma/template.phtml']);

        $this->assertEquals(
            ['/path/to/frontend/luma/template.phtml'],
            $this->model->getPatternDirs(['theme' => $theme])
        );
    }

    public function testGetPatternDirsUsesThemeDataAreaWhenRegisteredFullPathIsMissing()
    {
        $theme = $this->createThemeModelMock('adminhtml/Magento/luma', 'frontend', 'Magento/luma');
        $theme->expects($this->never())->method('getArea');
        $theme->expects($this->once())->method('getParentTheme')->willReturn(null);

        $this->componentRegistrarMock->expects($this->exactly(2))
            ->method('getPath')
            ->willReturnMap([
                [ComponentRegistrar::THEME, 'adminhtml/Magento/luma', null],
                [ComponentRegistrar::THEME, 'frontend/Magento/luma', '/path/to/frontend/luma'],
            ]);

        $this->ruleMock->expects($this->once())
            ->method('getPatternDirs')
            ->with(['theme_dir' => '/path/to/frontend/luma'])
            ->willReturn(['/path/to/frontend/luma/template.phtml']);

        $this->assertEquals(
            ['/path/to/frontend/luma/template.phtml'],
            $this->model->getPatternDirs(['theme' => $theme])
        );
    }

    /**
     * @param string $fullPath
     * @param string $area
     * @param string $themePath
     * @return ThemeModel|MockObject
     */
    private function createThemeModelMock(string $fullPath, string $area, string $themePath): ThemeModel
    {
        $theme = $this->getMockBuilder(ThemeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFullPath', 'getThemePath', 'getParentTheme', 'getArea'])
            ->getMock();
        $theme->method('getFullPath')->willReturn($fullPath);
        $theme->method('getThemePath')->willReturn($themePath);
        $theme->setData('area', $area);

        return $theme;
    }
}
