<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\View\File;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\File\Collector\Base
     */
    private $fileCollector;

    /**
     * @var \Magento\Framework\View\File\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactoryMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeMock;

    /**
     * @var \Magento\Framework\Component\DirSearch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dirSearch;

    protected function setUp()
    {
        $this->fileFactoryMock = $this->getMockBuilder('Magento\Framework\View\File\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeMock = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')
            ->setMethods(['getData'])
            ->getMockForAbstractClass();

        $this->dirSearch = $this->getMock('Magento\Framework\Component\DirSearch', [], [], '', false);

        $this->fileCollector = new \Magento\Framework\View\File\Collector\Base(
            $this->dirSearch,
            $this->fileFactoryMock,
            'layout'
        );
    }

    public function testGetFiles()
    {
        $files = [];
        foreach (['shared', 'theme'] as $fileType) {
            for ($i = 0; $i < 2; $i++) {
                $file = $this->getMock('\Magento\Framework\Component\ComponentFile', [], [], '', false);
                $file->expects($this->once())
                    ->method('getFullPath')
                    ->will($this->returnValue("{$fileType}/module/{$i}/path"));
                $file->expects($this->once())
                    ->method('getComponentName')
                    ->will($this->returnValue('Module_' . $i));
                $files[$fileType][] = $file;
            }
        }

        $this->dirSearch->expects($this->any())
            ->method('collectFilesWithContext')
            ->willReturnMap(
                [
                    [ComponentRegistrar::MODULE, 'view/base/layout/*.xml', $files['shared']],
                    [ComponentRegistrar::MODULE, 'view/frontend/layout/*.xml', $files['theme']]
                ]
            );
        $this->fileFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->createFileMock());
        $this->themeMock->expects($this->once())
            ->method('getData')
            ->with('area')
            ->willReturn('frontend');

        $result = $this->fileCollector->getFiles($this->themeMock, '*.xml');
        $this->assertCount(4, $result);
        $this->assertInstanceOf('Magento\Framework\View\File', $result[0]);
        $this->assertInstanceOf('Magento\Framework\View\File', $result[1]);
        $this->assertInstanceOf('Magento\Framework\View\File', $result[2]);
        $this->assertInstanceOf('Magento\Framework\View\File', $result[3]);
    }

    /**
     * Create file mock object
     *
     * @return \Magento\Framework\View\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFileMock()
    {
        return $this->getMockBuilder('Magento\Framework\View\File')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
