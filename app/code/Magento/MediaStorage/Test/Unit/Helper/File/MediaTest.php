<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Helper\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Helper\File\Media;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    const UPDATE_TIME = 'update_time';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $dirMock;

    /** @var  Media */
    protected $helper;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dirMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($this->dirMock));
        $dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $dateMock->expects($this->any())
            ->method('date')
            ->will($this->returnValue(self::UPDATE_TIME));
        $this->helper = $this->objectManager->getObject(
            'Magento\MediaStorage\Helper\File\Media',
            ['filesystem' => $filesystemMock, 'date' => $dateMock]
        );
    }

    /**
     * @param string $path
     * @param string $expectedDir
     * @param string $expectedFile
     * @dataProvider pathDataProvider
     */
    public function testCollectFileInfo($path, $expectedDir, $expectedFile)
    {
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';

        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->will($this->returnValue($relativePath));
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('readFile')
            ->with($relativePath)
            ->will($this->returnValue($content));

        $expected = [
            'filename' => $expectedFile,
            'content' => $content,
            'update_time' => self::UPDATE_TIME,
            'directory' => $expectedDir,
        ];

        $this->assertEquals($expected, $this->helper->collectFileInfo($mediaDirectory, $path));
    }

    public function pathDataProvider()
    {
        return [
            'file only' => ['filename', null, 'filename'],
            'with dir' => ['dir/filename', 'dir', 'filename'],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File mediaDir/path does not exist
     */
    public function testCollectFileInfoNotFile()
    {
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';
        $path = 'path';
        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->will($this->returnValue($relativePath));
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->will($this->returnValue(false));
        $this->dirMock->expects($this->never())
            ->method('isReadable')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->never())
            ->method('readFile')
            ->with($relativePath)
            ->will($this->returnValue($content));

        $this->helper->collectFileInfo($mediaDirectory, $path);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File mediaDir/path is not readable
     */
    public function testCollectFileInfoNotReadable()
    {
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';
        $path = 'path';
        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->will($this->returnValue($relativePath));
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->will($this->returnValue(false));
        $this->dirMock->expects($this->never())
            ->method('readFile')
            ->with($relativePath)
            ->will($this->returnValue($content));

        $this->helper->collectFileInfo($mediaDirectory, $path);
    }
}
