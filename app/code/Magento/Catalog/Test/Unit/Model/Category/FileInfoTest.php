<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;

class FileInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var Mime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mime;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaDirectory;

    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baseDirectory;

    /**
     * @var FileInfo
     */
    private $model;

    protected function setUp()
    {
        $this->mediaDirectory = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();

        $this->baseDirectory = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->baseDirectory);

        $this->mime = $this->getMockBuilder(Mime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->baseDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn('/a/b/c');

        $this->model = new FileInfo(
            $this->filesystem,
            $this->mime
        );
    }

    public function testGetMimeType()
    {
        $fileName = '/filename.ext1';
        $absoluteFilePath = '/a/b/c/pub/media/catalog/category/filename.ext1';

        $expected = 'ext1';

        $this->mediaDirectory->expects($this->at(0))
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn('/a/b/c/pub/media');

        $this->mediaDirectory->expects($this->at(1))
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn('/a/b/c/pub/media');

        $this->mediaDirectory->expects($this->at(2))
            ->method('getAbsolutePath')
            ->with('/catalog/category/filename.ext1')
            ->willReturn($absoluteFilePath);

        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($absoluteFilePath)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getMimeType($fileName));
    }

    public function testGetStat()
    {
        $mediaPath = '/catalog/category';

        $fileName = '/filename.ext1';

        $expected = ['size' => 1];

        $this->mediaDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn('/a/b/c/pub/media');

        $this->mediaDirectory->expects($this->once())
            ->method('stat')
            ->with($mediaPath . $fileName)
            ->willReturn($expected);

        $result = $this->model->getStat($fileName);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('size', $result);
        $this->assertEquals(1, $result['size']);
    }

    public function testIsExist()
    {
        $mediaPath = '/catalog/category';

        $fileName = '/filename.ext1';

        $this->mediaDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn('/a/b/c/pub/media');

        $this->mediaDirectory->expects($this->once())
            ->method('isExist')
            ->with($mediaPath . $fileName)
            ->willReturn(true);

        $this->assertTrue($this->model->isExist($fileName));
    }
}
