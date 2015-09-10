<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector\Override;

use Magento\Framework\View\File\Collector\Override\Base;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\View\File\Factory;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Base
     */
    private $model;

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
    private $readFactoryMock;

    /**
     * Component registry
     *
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrarMock;

    protected function setUp()
    {
        $this->pathPatternHelperMock = $this->getMockBuilder('Magento\Framework\View\Helper\PathPattern')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMock('Magento\Framework\Filesystem', ['getDirectoryRead'], [], '', false);
        $this->readFactoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadFactory')
            ->disableOriginalConstructor()->getMock();
        $this->componentRegistrarMock = $this->getMockBuilder('Magento\Framework\Component\ComponentRegistrarInterface')
            ->disableOriginalConstructor()->getMock();
        $this->fileFactory = $this->getMock('Magento\Framework\View\File\Factory', [], [], '', false);
        $this->model = new \Magento\Framework\View\File\Collector\Override\Base(
            $filesystem,
            $this->fileFactory,
            $this->pathPatternHelperMock,
            $this->componentRegistrarMock,
            $this->readFactoryMock,
            'override'
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

        $handlePath = 'design/area/theme/path/%s/override/%s';
        $returnKeys = [];
        foreach ($files as $file) {
            $returnKeys[] = sprintf($handlePath, $file['module'], $file['handle']);
        }
        $readerMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')->getMock();
        $this->readFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($readerMock));
        $this->componentRegistrarMock->expects($this->once())
            ->method('getPath');
        $readerMock->expects($this->once())
            ->method('search')
            ->will($this->returnValue($returnKeys));
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($filePath)
            ->willReturn($pathPattern);
        $checkResult = [];
        foreach ($files as $key => $file) {
            $checkResult[$key] = new \Magento\Framework\View\File($file['handle'], $file['module']);
            $this->fileFactory
                ->expects($this->at($key))
                ->method('create')
                ->with(sprintf($handlePath, $file['module'], $file['handle']), $file['module'])
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
