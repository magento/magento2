<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\View\File\Collector\ThemeModular;
use Magento\Framework\App\Filesystem\DirectoryList;
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
    private $directory;

    /**
     * @var Factory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactory;

    /**
     * @var \Magento\Framework\View\Helper\PathPattern|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pathPatternHelperMock;

    protected function setUp()
    {
        $this->directory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            ['getAbsolutePath', 'search'],
            [],
            '',
            false
        );
        $this->pathPatternHelperMock = $this->getMockBuilder('Magento\Framework\View\Helper\PathPattern')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMock(
            'Magento\Framework\Filesystem',
            ['getDirectoryRead', '__wakeup'],
            [],
            '',
            false
        );
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($this->directory);
        $this->fileFactory = $this->getMock('Magento\Framework\View\File\Factory', [], [], '', false);
        $this->model = new \Magento\Framework\View\File\Collector\ThemeModular(
            $filesystem,
            $this->fileFactory,
            $this->pathPatternHelperMock,
            'subdir'
        );
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
        $theme->expects($this->once())->method('getFullPath')->willReturn('area/theme/path');

        $handlePath = 'design/area/theme/path/%s/subdir/%s';
        $returnKeys = [];
        foreach ($files as $file) {
            $returnKeys[] = sprintf($handlePath, $file['module'], $file['handle']);
        }

        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($filePath)
            ->willReturn($pathPattern);
        $this->directory->expects($this->once())
            ->method('search')
            ->willReturn($returnKeys);
        $this->directory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);

        $checkResult = [];
        foreach ($files as $key => $file) {
            $checkResult[$key] = new \Magento\Framework\View\File($file['handle'], $file['module'], $theme);
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
