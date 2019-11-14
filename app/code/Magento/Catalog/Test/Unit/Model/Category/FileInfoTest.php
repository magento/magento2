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
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/**
 * Test for Magento\Catalog\Model\Category\FileInfo class.
 */
class FileInfoTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var Mime|MockObject
     */
    private $mime;

    /**
     * @var WriteInterface|MockObject
     */
    private $mediaDirectory;

    /**
     * @var ReadInterface|MockObject
     */
    private $baseDirectory;

    /**
     * @var ReadInterface|MockObject
     */
    private $pubDirectory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var FileInfo
     */
    private $model;

    protected function setUp()
    {
        $this->mediaDirectory = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();

        $this->baseDirectory = $baseDirectory = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();

        $this->pubDirectory = $pubDirectory = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->filesystem->method('getDirectoryRead')
            ->willReturnCallback(
                function ($arg) use ($baseDirectory, $pubDirectory) {
                    if ($arg === DirectoryList::PUB) {
                        return $pubDirectory;
                    }
                    return $baseDirectory;
                }
            );

        $this->mime = $this->getMockBuilder(Mime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->baseDirectory->method('getAbsolutePath')
            ->willReturn('/a/b/c/');

        $this->baseDirectory->method('getRelativePath')
            ->with('/a/b/c/pub/')
            ->willReturn('pub/');

        $this->pubDirectory->method('getAbsolutePath')
            ->willReturn('/a/b/c/pub/');

        $this->model = new FileInfo(
            $this->filesystem,
            $this->mime,
            $this->storeManager
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
            ->willReturn('/a/b/c/pub/media/');

        $this->mediaDirectory->expects($this->at(1))
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn('/a/b/c/pub/media/');

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

        $this->mediaDirectory->method('getAbsolutePath')
            ->with(null)
            ->willReturn('/a/b/c/pub/media/');

        $this->mediaDirectory->method('stat')
            ->with($mediaPath . $fileName)
            ->willReturn($expected);

        $result = $this->model->getStat($fileName);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('size', $result);
        $this->assertEquals(1, $result['size']);
    }

    /**
     * @param $fileName
     * @param $fileMediaPath
     * @dataProvider isExistProvider
     */
    public function testIsExist($fileName, $fileMediaPath)
    {
        $this->mediaDirectory->method('getAbsolutePath')
            ->willReturn('/a/b/c/pub/media/');

        $this->mediaDirectory->method('isExist')
            ->with($fileMediaPath)
            ->willReturn(true);

        $this->assertTrue($this->model->isExist($fileName));
    }

    /**
     * @return array
     */
    public function isExistProvider()
    {
        return [
            ['/filename.ext1', '/catalog/category/filename.ext1'],
            ['/pub/media/filename.ext1', 'filename.ext1'],
            ['/media/filename.ext1', 'filename.ext1']
        ];
    }

    /**
     * @param $fileName
     * @param $expected
     * @dataProvider isBeginsWithMediaDirectoryPathProvider
     */
    public function testIsBeginsWithMediaDirectoryPath($fileName, $expected)
    {
        $this->mediaDirectory->method('getAbsolutePath')
            ->willReturn('/a/b/c/pub/media/');

        $this->assertEquals($expected, $this->model->isBeginsWithMediaDirectoryPath($fileName));
    }

    /**
     * @return array
     */
    public function isBeginsWithMediaDirectoryPathProvider()
    {
        return [
            ['/pub/media/test/filename.ext1', true],
            ['/media/test/filename.ext1', true],
            ['/test/filename.ext1', false],
            ['test2/filename.ext1', false]
        ];
    }
}
