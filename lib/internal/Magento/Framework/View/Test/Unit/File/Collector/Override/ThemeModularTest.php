<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector\Override;

use Magento\Framework\Component\ComponentRegistrar;

class ThemeModularTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\File\Collector\Override\ThemeModular
     */
    private $model;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeDirectory;

    /**
     * @var \Magento\Framework\View\File\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactory;

    /**
     * @var \Magento\Framework\View\Helper\PathPattern|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pathPatternHelperMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readDirFactory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    protected function setUp()
    {
        $this->themeDirectory = $this->getMock(\Magento\Framework\Filesystem\Directory\Read::class, [], [], '', false);
        $this->themeDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);
        $this->pathPatternHelperMock = $this->getMockBuilder(\Magento\Framework\View\Helper\PathPattern::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileFactory = $this->getMock(\Magento\Framework\View\File\Factory::class, [], [], '', false);
        $this->readDirFactory = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadFactory::class,
            [],
            [],
            '',
            false
        );
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->themeDirectory));
        $this->componentRegistrar = $this->getMockForAbstractClass(
            \Magento\Framework\Component\ComponentRegistrarInterface::class
        );
        $this->model = new \Magento\Framework\View\File\Collector\Override\ThemeModular(
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
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('area/Vendor/theme'));
        $this->assertSame([], $this->model->getFiles($theme, ''));
    }

    public function testGetFiles()
    {
        $themePath = 'area/theme_path';
        $inputPath = '*.xml';
        $grandparentTheme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $grandparentTheme->expects($this->once())->method('getCode')->willReturn('vendor/grand_parent_theme');

        $parentTheme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $parentTheme->expects($this->once())->method('getCode')->willReturn('vendor/parent_theme');
        $parentTheme->expects($this->once())->method('getParentTheme')->willReturn($grandparentTheme);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
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

        $fileOne = $this->getMock(\Magento\Framework\View\File::class, [], [], '', false);
        $fileTwo = $this->getMock(\Magento\Framework\View\File::class, [], [], '', false);
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
        $grandparentTheme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $grandparentTheme->expects($this->once())->method('getCode')->willReturn('vendor/grand_parent_theme');

        $parentTheme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $parentTheme->expects($this->once())->method('getCode')->willReturn('vendor/parent_theme');
        $parentTheme->expects($this->once())->method('getParentTheme')->willReturn($grandparentTheme);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->expects($this->once())->method('getFullPath')->willReturn($themePath);
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);

        $filePathOne = 'design/area/theme_path/Module_Two/override/theme/vendor/grand_parent_theme/preset/3.xml';
        $this->themeDirectory->expects($this->once())
            ->method('search')
            ->with('*_*/override/theme/*/*/preset/3.xml')
            ->willReturn([$filePathOne]);

        $fileOne = $this->getMock(\Magento\Framework\View\File::class, [], [], '', false);
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
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class, $expectedMessage);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
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
