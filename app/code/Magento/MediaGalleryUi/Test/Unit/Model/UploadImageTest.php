<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Unit\Model;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGalleryUi\Model\UploadImage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Provides test for upload image functionality
 */
class UploadImageTest extends TestCase
{
    /**
     * @var Storage|MockObject
     */
    private $imagesStorageMock;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var Read|MockObject
     */
    private $mediaDirectoryMock;

    /**
     * @var UploadImage
     */
    private $uploadImage;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->imagesStorageMock = $this->createMock(Storage::class);
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->mediaDirectoryMock = $this->createMock(Read::class);

        $this->uploadImage = (new ObjectManager($this))->getObject(
            UploadImage::class,
            [
                'imagesStorage' => $this->imagesStorageMock,
                'filesystem' => $this->fileSystemMock,
            ]
        );
    }

    /**
     * Test successful image file upload.
     *
     * @param string $targetFolder
     * @param string|null $type
     * @param string $absolutePath
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(string $targetFolder, ?string $type, string $absolutePath): void
    {
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->mediaDirectoryMock->expects($this->once())
            ->method('isDirectory')
            ->with($targetFolder)
            ->willReturn(true);

        $this->mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($targetFolder)
            ->willReturn($absolutePath);

        $uploadResult = ['path' => 'media/catalog', 'file' => 'test-image.jpeg'];
        $this->imagesStorageMock->expects($this->once())
            ->method('uploadFile')
            ->with($absolutePath, $type)
            ->willReturn($uploadResult);

        $this->uploadImage->execute($targetFolder, $type);
    }

    /**
     * Test upload image method with logical exception when the folder is not a folder.
     */
    public function testExecuteWithException(): void
    {
        $targetFolder = 'not-a-folder';
        $type = 'image';
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->mediaDirectoryMock->expects($this->once())
            ->method('isDirectory')
            ->with($targetFolder)
            ->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Directory not-a-folder does not exist in media directory.');

        $this->uploadImage->execute($targetFolder, $type);
    }

    /**
     * Provides test case data.
     *
     * @return array
     */
    public static function executeDataProvider(): array
    {
        return [
            [
                'targetFolder' => 'media/catalog',
                'type' => 'image',
                'absolutePath' => 'root/media/catalog/test-image.jpeg'
            ]
        ];
    }
}
