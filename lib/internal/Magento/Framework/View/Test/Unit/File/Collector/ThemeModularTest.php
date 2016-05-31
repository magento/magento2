<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\View\File\Collector\ThemeModular;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\View\File\Factory;

class ThemeModularTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeModular
     */
    private $model;

    /**
     * @var Read | \PHPUnit_Framework_MockObject_MockObject
     */
    private $themeDirectory;

    /**
     * @var Factory | \PHPUnit_Framework_MockObject_MockObject
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
        $this->themeDirectory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            ['getAbsolutePath', 'search'],
            [],
            '',
            false
        );
        $this->pathPatternHelperMock = $this->getMockBuilder('Magento\Framework\View\Helper\PathPattern')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactory = $this->getMock('Magento\Framework\View\File\Factory', [], [], '', false);
        $this->readDirFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->themeDirectory));
        $this->componentRegistrar = $this->getMockForAbstractClass(
            'Magento\Framework\Component\ComponentRegistrarInterface'
        );
        $this->model = new \Magento\Framework\View\File\Collector\ThemeModular(
            $this->fileFactory,
            $this->readDirFactory,
            $this->componentRegistrar,
            $this->pathPatternHelperMock,
            'subdir'
        );
    }

    public function testGetFilesWrongTheme()
    {
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue(''));
        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('area/Vendor/theme'));
        $this->assertSame([], $this->model->getFiles($theme, ''));
    }

    /**
     * @param array $files
     * @param string $filePath
     * @param string $pathPattern
     *
     * @dataProvider getFilesDataProvider
     */
    public function testGetFiles($files, $filePath, $pathPattern)
    {
        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $themePath = 'area/theme/path';
        $theme->expects($this->once())->method('getFullPath')->willReturn($themePath);

        $handlePath = 'design/area/theme/path/%s/subdir/%s';
        $returnKeys = [];
        foreach ($files as $file) {
            $returnKeys[] = sprintf($handlePath, $file['module'], $file['handle']);
        }

        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themePath)
            ->will($this->returnValue('/full/theme/path'));
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($filePath)
            ->willReturn($pathPattern);
        $this->themeDirectory->expects($this->once())
            ->method('search')
            ->willReturn($returnKeys);
        $this->themeDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);

        $checkResult = [];
        foreach ($files as $key => $file) {
            $checkResult[$key] = new \Magento\Framework\View\File($file['handle'], $file['module'], $theme);
            $checkResult[$key] = $this->getMock('Magento\Framework\View\File', [], [], '', false);
            $this->fileFactory
                ->expects($this->at($key))
                ->method('create')
                ->with(sprintf($handlePath, $file['module'], $file['handle']), $file['module'], $theme)
                ->willReturn($checkResult[$key]);
        }
        $this->assertSame($checkResult, $this->model->getFiles($theme, $filePath));
    }

    /**
     * @return array
     */
    public function getFilesDataProvider()
    {
        return [
            [
                [
                    ['handle' => '1.xml', 'module' => 'Module_One'],
                    ['handle' => '2.xml', 'module' => 'Module_One'],
                    ['handle' => '3.xml', 'module' => 'Module_Two'],
                ],
                '*.xml',
                '[^/]*\\.xml'
            ],
            [
                [
                    ['handle' => 'preset/4', 'module' => 'Module_Fourth'],
                ],
                'preset/4',
                'preset/4'
            ],
        ];
    }
}
