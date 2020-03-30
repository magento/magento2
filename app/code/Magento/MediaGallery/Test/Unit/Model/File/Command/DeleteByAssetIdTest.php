<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\File\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByIdInterface;
use Magento\MediaGallery\Model\File\Command\DeleteByAssetId;

/**
 * Test the DeleteByAssetIdTest command model
 */
class DeleteByAssetIdTest extends TestCase
{
    /**
     * @var MockObject|Filesystem
     */
    private $filesystem;

    /**
     * @var MockObject|Storage
     */
    private $storage;

    /**
     * @var MockObject|GetByIdInterface
     */
    private $getById;

    /**
     * @var DeleteByAssetId
     */
    private $object;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->storage = $this->createMock(Storage::class);
        $this->getById = $this->createMock(GetByIdInterface::class);

        $this->object = (new ObjectManager($this))->getObject(
            DeleteByAssetId::class,
            [
                'filesystem' => $this->filesystem,
                'imagesStorage' => $this->storage,
                'getAssetById' => $this->getById
            ]
        );
    }

    /**
     * Test delete file by asset id
     */
    public function testExecute(): void
    {
        $assetId = 42;
        $path = '/file1.jpg';
        $absoluteMediaPath = '/var/www/html/pub/media';

        $asset = $this->createMock(AssetInterface::class);
        $asset->expects($this->once())
            ->method('getPath')
            ->willReturn($path);

        $this->getById->expects($this->once())
            ->method('execute')
            ->with($assetId)
            ->willReturn($asset);

        $directory = $this->createMock(Read::class);
        $directory->expects($this->once())
            ->method('isFile')
            ->willReturn(true);
        $directory->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn($absoluteMediaPath);

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($directory);

        $this->storage->expects($this->once())
            ->method('deleteFile')
            ->with($absoluteMediaPath . $path);

        $this->object->execute($assetId);
    }
}
