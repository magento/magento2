<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector\Override;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\File\Collector\Override\ThemeModular;
use Magento\Framework\Filesystem\Directory\Read;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\File\Factory;
use Magento\Framework\View\Helper\PathPattern;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Component\ComponentRegistrar;

class ThemeModularTest extends TestCase
{
    /**
     * @var ThemeModular
     */
    private $model;

    /**
     * @var Read|MockObject
     */
    private $themeDirectory;

    /**
     * @var Factory|MockObject
     */
    private $fileFactory;

    /**
     * @var PathPattern|MockObject
     */
    protected $pathPatternHelperMock;

    /**
     * @var ReadFactory|MockObject
     */
    private $readDirFactory;

    /**
     * @var ComponentRegistrarInterface|MockObject
     */
    private $componentRegistrar;

    protected function setUp(): void
    {
        $this->themeDirectory = $this->createMock(Read::class);
        $this->themeDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);
        $this->pathPatternHelperMock = $this->getMockBuilder(PathPattern::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileFactory = $this->createMock(Factory::class);
        $this->readDirFactory = $this->createMock(ReadFactory::class);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->themeDirectory));
        $this->componentRegistrar = $this->getMockForAbstractClass(
            ComponentRegistrarInterface::class
        );
        $this->model = new ThemeModular(
            $this->fileFactory,
            $this->readDirFactory,
            $this->componentRegistrar,
            $this->pathPatternHelperMock,
            'override/theme'
        );
    }

    public function testGetFilesWrongTheme()
    {
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue(''));
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('area/Vendor/theme'));
        $this->assertSame([], $this->model->getFiles($theme, ''));
    }

    public function testGetFiles()
    {
        $themePath = 'area/theme_path';
        $inputPath = '*.xml';
        $grandparentTheme = $this->getMockForAbstractClass(ThemeInterface::class);
        $grandparentTheme->expects($this->once())->method('getCode')->willReturn('vendor/grand_parent_theme');

        $parentTheme = $this->getMockForAbstractClass(ThemeInterface::class);
        $parentTheme->expects($this->once())->method('getCode')->willReturn('vendor/parent_theme');
        $parentTheme->expects($this->once())->method('getParentTheme')->willReturn($grandparentTheme);

        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())->method('getFullPath')->willReturn($themePath);
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);

        $filePathOne = 'design/area/theme_path/Module_One/override/theme/vendor/parent_theme/1.xml';
        $filePathTwo = 'design/area/theme_path/Module_Two/override/theme/vendor/grand_parent_theme/2.xml';
        $this->themeDirectory->expects($this->once())
            ->method('search')
            ->with('*_*/override/theme/*/*/*.xml')
            ->willReturn([$filePathOne, $filePathTwo]);
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($inputPath)
            ->willReturn('[^/]*\\.xml');

        $fileOne = $this->createMock(File::class);
        $fileTwo = $this->createMock(File::class);
        $this->fileFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [$filePathOne, 'Module_One', $parentTheme, false, $fileOne],
                    [$filePathTwo, 'Module_Two', $grandparentTheme, false, $fileTwo],
                ]
            );
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themePath)
            ->will($this->returnValue('/full/theme/path'));

        $this->assertSame([$fileOne, $fileTwo], $this->model->getFiles($theme, $inputPath));
    }

    public function testGetFilesWithPreset()
    {
        $themePath = 'area/theme_path';
        $inputPath = 'preset/3.xml';
        $grandparentTheme = $this->getMockForAbstractClass(ThemeInterface::class);
        $grandparentTheme->expects($this->once())->method('getCode')->willReturn('vendor/grand_parent_theme');

        $parentTheme = $this->getMockForAbstractClass(ThemeInterface::class);
        $parentTheme->expects($this->once())->method('getCode')->willReturn('vendor/parent_theme');
        $parentTheme->expects($this->once())->method('getParentTheme')->willReturn($grandparentTheme);

        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())->method('getFullPath')->willReturn($themePath);
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);

        $filePathOne = 'design/area/theme_path/Module_Two/override/theme/vendor/grand_parent_theme/preset/3.xml';
        $this->themeDirectory->expects($this->once())
            ->method('search')
            ->with('*_*/override/theme/*/*/preset/3.xml')
            ->willReturn([$filePathOne]);

        $fileOne = $this->createMock(File::class);
        $this->fileFactory
            ->expects($this->once())
            ->method('create')
            ->with($filePathOne, 'Module_Two', $grandparentTheme)
            ->willReturn($fileOne);
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($inputPath)
            ->willReturn('preset/3.xml');
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themePath)
            ->will($this->returnValue('/full/theme/path'));

        $this->assertSame([$fileOne], $this->model->getFiles($theme, $inputPath));
    }

    public function testGetFilesWrongAncestor()
    {
        $themePath = 'area/theme_path';
        $inputPath = '*.xml';
        $filePath = 'design/area/theme_path/Module_One/override/theme/vendor/parent_theme/1.xml';
        $expectedMessage = "Trying to override modular view file '$filePath' for theme 'vendor/parent_theme'"
            . ", which is not ancestor of theme 'vendor/theme_path'";
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($expectedMessage);

        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())->method('getFullPath')->willReturn($themePath);
        $theme->expects($this->once())->method('getParentTheme')->willReturn(null);
        $theme->expects($this->once())->method('getCode')->willReturn('vendor/theme_path');

        $this->themeDirectory->expects($this->once())
            ->method('search')
            ->with('*_*/override/theme/*/*/*.xml')
            ->willReturn([$filePath]);
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($inputPath)
            ->willReturn('[^/]*\\.xml');
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themePath)
            ->will($this->returnValue('/full/theme/path'));

        $this->model->getFiles($theme, $inputPath);
    }
}
