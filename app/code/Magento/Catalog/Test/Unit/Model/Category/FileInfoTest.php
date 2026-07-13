<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    protected function setUp(): void
    {
        $this->mediaDirectory = $this->createMock(WriteInterface::class);

        $this->baseDirectory = $baseDirectory = $this->createMock(ReadInterface::class);

        $this->pubDirectory = $pubDirectory = $this->createMock(ReadInterface::class);

        $this->store = $this->createMock(Store::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->method('getStore')->willReturn($this->store);

        $this->filesystem = $this->createMock(Filesystem::class);

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

        $this->mime = $this->createMock(Mime::class);

        $this->baseDirectory->method('getAbsolutePath')
            ->willReturn('/a/b/c/');

        $this->baseDirectory->method('getRelativePath')
            ->with('/a/b/c/pub/')
            ->willReturn('pub/');

        $this->pubDirectory->method('getAbsolutePath')
            ->willReturn('/a/b/c/pub/');

        $this->store->method('getBaseUrl')
            ->willReturn('https://example.com/');

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
        $this->mediaDirectory->method('getAbsolutePath')
            ->willReturnMap(
                [
                    [null, '/a/b/c/pub/media'],
                    ['/catalog/category/filename.ext1', $absoluteFilePath]
                ]
            );

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

        $this->assertIsArray($result);
        $this->assertArrayHasKey('size', $result);
        $this->assertEquals(1, $result['size']);
    }

    /**
     * @param $fileName
     * @param $fileMediaPath
     */
    #[DataProvider('isExistProvider')]
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
    public static function isExistProvider()
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
     */
    #[DataProvider('isBeginsWithMediaDirectoryPathProvider')]
    public function testIsBeginsWithMediaDirectoryPath($fileName, $expected)
    {
        $this->mediaDirectory->method('getAbsolutePath')
            ->willReturn('/a/b/c/pub/media/');

        $this->assertEquals($expected, $this->model->isBeginsWithMediaDirectoryPath($fileName));
    }

    /**
     * @return array
     */
    public static function isBeginsWithMediaDirectoryPathProvider()
    {
        return [
            ['/pub/media/test/filename.ext1', true],
            ['/media/test/filename.ext1', true],
            ['/test/filename.ext1', false],
            ['test2/filename.ext1', false]
        ];
    }
}
