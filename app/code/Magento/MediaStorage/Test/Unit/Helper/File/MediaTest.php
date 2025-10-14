<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Helper\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    const UPDATE_TIME = 'update_time';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /** @var ReadInterface|MockObject  */
    protected $dirMock;

    /** @var  Media */
    protected $helper;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->dirMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->dirMock);
        $dateMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateMock->expects($this->any())
            ->method('date')
            ->willReturn(self::UPDATE_TIME);
        $this->helper = $this->objectManager->getObject(
            Media::class,
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
            ->willReturn($relativePath);
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('readFile')
            ->with($relativePath)
            ->willReturn($content);

        $expected = [
            'filename' => $expectedFile,
            'content' => $content,
            'update_time' => self::UPDATE_TIME,
            'directory' => $expectedDir,
        ];

        $this->assertEquals($expected, $this->helper->collectFileInfo($mediaDirectory, $path));
    }

    /**
     * @return array
     */
    public static function pathDataProvider()
    {
        return [
            'file only' => ['filename', null, 'filename'],
            'with dir' => ['dir/filename', 'dir', 'filename'],
        ];
    }

    public function testCollectFileInfoNotFile()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The "mediaDir/path" file doesn\'t exist. Verify the file and try again.');
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';
        $path = 'path';
        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->willReturn($relativePath);
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->willReturn(false);
        $this->dirMock->expects($this->never())
            ->method('isReadable')
            ->with($relativePath)
            ->willReturn(true);
        $this->dirMock->expects($this->never())
            ->method('readFile')
            ->with($relativePath)
            ->willReturn($content);

        $this->helper->collectFileInfo($mediaDirectory, $path);
    }

    public function testCollectFileInfoNotReadable()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('File mediaDir/path is not readable');
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';
        $path = 'path';
        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->willReturn($relativePath);
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->willReturn(false);
        $this->dirMock->expects($this->never())
            ->method('readFile')
            ->with($relativePath)
            ->willReturn($content);

        $this->helper->collectFileInfo($mediaDirectory, $path);
    }
}
