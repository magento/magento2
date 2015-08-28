<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector\Override;

use Magento\Framework\App\Filesystem\DirectoryList;

class ThemeModularTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\File\Collector\Override\ThemeModular
     */
    private $model;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directory;

    /**
     * @var \Magento\Framework\View\File\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactory;

    /**
     * @var \Magento\Framework\View\Helper\PathPattern|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pathPatternHelperMock;

    protected function setUp()
    {
        $filesystem = $this->getMock('Magento\Framework\Filesystem', ['getDirectoryRead'], [], '', false);
        $this->directory = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->directory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);
        $this->pathPatternHelperMock = $this->getMockBuilder('Magento\Framework\View\Helper\PathPattern')
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem->expects($this->any())->method('getDirectoryRead')
            ->with($this->equalTo(DirectoryList::THEMES))
            ->willReturn($this->directory);
        $this->fileFactory = $this->getMock('Magento\Framework\View\File\Factory', [], [], '', false);
        $this->model = new \Magento\Framework\View\File\Collector\Override\ThemeModular(
            $filesystem,
            $this->fileFactory,
            $this->pathPatternHelperMock,
            'override/theme'
        );
    }

    public function testGetFiles()
    {
        $inputPath = '*.xml';
        $grandparentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $grandparentTheme->expects($this->once())->method('getCode')->willReturn('vendor/grand_parent_theme');

        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->once())->method('getCode')->willReturn('vendor/parent_theme');
        $parentTheme->expects($this->once())->method('getParentTheme')->willReturn($grandparentTheme);

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->willReturn('area/theme_path');
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);

        $filePathOne = 'design/area/theme_path/Module_One/override/theme/vendor/parent_theme/1.xml';
        $filePathTwo = 'design/area/theme_path/Module_Two/override/theme/vendor/grand_parent_theme/2.xml';
        $this->directory->expects($this->once())
            ->method('search')
            ->with($this->equalTo('area/theme_path/*_*/override/theme/*/*/*.xml'))
            ->willReturn([$filePathOne, $filePathTwo]);
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($inputPath)
            ->willReturn('[^/]*\\.xml');

        $fileOne = new \Magento\Framework\View\File('1.xml', 'Module_One', $parentTheme);
        $fileTwo = new \Magento\Framework\View\File('2.xml', 'Module_Two', $grandparentTheme);
        $this->fileFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [$filePathOne, 'Module_One', $parentTheme, false, $fileOne],
                    [$filePathTwo, 'Module_Two', $grandparentTheme, false, $fileTwo],
                ]
            );

        $this->assertSame([$fileOne, $fileTwo], $this->model->getFiles($theme, $inputPath));
    }

    public function testGetFilesWithPreset()
    {
        $inputPath = 'preset/3.xml';
        $grandparentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $grandparentTheme->expects($this->once())->method('getCode')->willReturn('vendor/grand_parent_theme');

        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->once())->method('getCode')->willReturn('vendor/parent_theme');
        $parentTheme->expects($this->once())->method('getParentTheme')->willReturn($grandparentTheme);

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->willReturn('area/theme_path');
        $theme->expects($this->once())->method('getParentTheme')->willReturn($parentTheme);

        $filePathOne = 'design/area/theme_path/Module_Two/override/theme/vendor/grand_parent_theme/preset/3.xml';
        $this->directory->expects($this->once())
            ->method('search')
            ->with('area/theme_path/*_*/override/theme/*/*/preset/3.xml')
            ->willReturn([$filePathOne]);

        $fileOne = new \Magento\Framework\View\File('3.xml', 'Module_Two', $grandparentTheme);
        $this->fileFactory
            ->expects($this->once())
            ->method('create')
            ->with($filePathOne, 'Module_Two', $grandparentTheme)
            ->willReturn($fileOne);
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($inputPath)
            ->willReturn('preset/3.xml');

        $this->assertSame([$fileOne], $this->model->getFiles($theme, $inputPath));
    }

    public function testGetFilesWrongAncestor()
    {
        $inputPath = '*.xml';
        $filePath = 'design/area/theme_path/Module_One/override/theme/vendor/parent_theme/1.xml';
        $expectedMessage = "Trying to override modular view file '$filePath' for theme 'vendor/parent_theme'"
            . ", which is not ancestor of theme 'vendor/theme_path'";
        $this->setExpectedException('Magento\Framework\Exception\LocalizedException', $expectedMessage);

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->willReturn('area/theme_path');
        $theme->expects($this->once())->method('getParentTheme')->willReturn(null);
        $theme->expects($this->once())->method('getCode')->willReturn('vendor/theme_path');

        $this->directory->expects($this->once())
            ->method('search')
            ->with('area/theme_path/*_*/override/theme/*/*/*.xml')
            ->willReturn([$filePath]);
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($inputPath)
            ->willReturn('[^/]*\\.xml');

        $this->model->getFiles($theme, $inputPath);
    }
}
