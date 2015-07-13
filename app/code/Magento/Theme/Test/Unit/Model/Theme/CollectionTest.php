<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    protected $model;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Config\ThemeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeConfigFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directory;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactory;

    protected function setUp()
    {
        $this->entityFactory = $this->getMockBuilder('Magento\Framework\Data\Collection\EntityFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeConfigFactory = $this->getMockBuilder('Magento\Framework\Config\ThemeFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->directory);

        $this->model = new Collection(
            $this->entityFactory,
            $this->filesystem,
            $this->themeConfigFactory
        );
    }

    /**
     * @test
     * @return void
     */
    public function testLoadData()
    {
        $relativeDir = 'dir';
        $fileContent = 'content file';
        $media = ['preview_image' => 'preview.jpg'];
        $themeTitle = 'Theme title';
        $themeConfigs = ['frontend/theme/code'];
        $themeConfig = $this->getMockBuilder('Magento\Framework\Config\Theme')->disableOriginalConstructor()->getMock();
        $theme = $this->getMockBuilder('Magento\Theme\Model\Theme')->disableOriginalConstructor()->getMock();
        $parentTheme = ['parentThemeCode'];
        $parentThemePath = 'frontend/parent/theme';

        $this->directory->expects($this->once())
            ->method('search')
            ->with($relativeDir)
            ->willReturn($themeConfigs);
        $this->directory->expects($this->any())
            ->method('isExist')
            ->with($themeConfigs[0])
            ->willReturn(true);
        $this->directory->expects($this->any())
            ->method('readFile')
            ->with($themeConfigs[0])
            ->willReturn($fileContent);
        $this->directory->expects($this->any())
            ->method('getRelativePath')
            ->with($themeConfigs[0])
            ->willReturn($themeConfigs[0]);
        $this->directory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnMap(
                [
                    [$themeConfigs[0], $themeConfigs[0]],
                    [null, ''],
                ]
            );
        $this->themeConfigFactory->expects($this->once())
            ->method('create')
            ->with(['configContent' => $fileContent])
            ->willReturn($themeConfig);
        $this->directory->expects($this->at(1))
            ->method('getAbsolutePath')
            ->willReturn('');
        $this->entityFactory->expects($this->any())
            ->method('create')
            ->with('Magento\Theme\Model\Theme')
            ->willReturn($theme);
        $themeConfig->expects($this->once())
            ->method('getMedia')
            ->willReturn($media);
        $themeConfig->expects($this->once())
            ->method('getParentTheme')
            ->willReturn($parentTheme);
        $themeConfig->expects($this->once())
            ->method('getThemeTitle')
            ->willReturn($themeTitle);
        $theme->expects($this->once())
            ->method('addData')
            ->with(
                [
                    'parent_id' => null,
                    'type' => ThemeInterface::TYPE_PHYSICAL,
                    'area' => 'frontend',
                    'theme_path' => 'theme',
                    'code' => 'theme',
                    'theme_title' => $themeTitle,
                    'preview_image' => $media['preview_image'],
                    'parent_theme_path' => $parentTheme[0]
                ]
            )
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getData')
            ->with('parent_theme_path')
            ->willReturn($parentThemePath);
        $theme->expects($this->once())
            ->method('getArea')
            ->willReturn('frontend');

        $this->model->addTargetPattern($relativeDir);
        $this->assertInstanceOf(get_class($this->model), $this->model->loadData());
    }

    /**
     * @test
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify at least one target pattern to theme config file.
     */
    public function testGetTargetPatternsException()
    {
        $this->model->getTargetPatterns();
    }
}
