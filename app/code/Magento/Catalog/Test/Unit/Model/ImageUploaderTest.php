<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImageUploaderTest extends TestCase
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * Core file storage database
     *
     * @var Database|MockObject
     */
    private $coreFileStorageDatabaseMock;

    /**
     * Media directory object (writable).
     *
     * @var Filesystem|MockObject
     */
    private $mediaDirectoryMock;

    /**
     * Media directory object (writable).
     *
     * @var WriteInterface|MockObject
     */
    private $mediaWriteDirectoryMock;

    /**
     * Uploader factory
     *
     * @var UploaderFactory|MockObject
     */
    private $uploaderFactoryMock;

    /**
     * Store manager
     *
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * Base tmp path
     *
     * @var string
     */
    private $baseTmpPath;

    /**
     * Base path
     *
     * @var string
     */
    private $basePath;

    /**
     * Allowed extensions
     *
     * @var array
     */
    private $allowedExtensions;

    /**
     * Allowed mime types
     *
     * @var array
     */
    private $allowedMimeTypes;

    protected function setUp(): void
    {
        $this->coreFileStorageDatabaseMock = $this->createMock(
            Database::class
        );
        $this->mediaDirectoryMock = $this->createMock(
            Filesystem::class
        );
        $this->mediaWriteDirectoryMock = $this->createMock(
            WriteInterface::class
        );
        $this->mediaDirectoryMock->expects($this->any())->method('getDirectoryWrite')->willReturn(
            $this->mediaWriteDirectoryMock
        );
        $this->uploaderFactoryMock = $this->createMock(
            UploaderFactory::class
        );
        $this->storeManagerMock = $this->createMock(
            StoreManagerInterface::class
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->baseTmpPath = 'base/tmp/';
        $this->basePath =  'base/real/';
        $this->allowedExtensions = ['.jpg'];
        $this->allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'];

        $this->imageUploader =
            new ImageUploader(
                $this->coreFileStorageDatabaseMock,
                $this->mediaDirectoryMock,
                $this->uploaderFactoryMock,
                $this->storeManagerMock,
                $this->loggerMock,
                $this->baseTmpPath,
                $this->basePath,
                $this->allowedExtensions,
                $this->allowedMimeTypes
            );
    }

    public function testSaveFileToTmpDir()
    {
        $fileId = 'file.jpg';
        $allowedMimeTypes = [
            'image/jpg',
            'image/jpeg',
            'image/gif',
            'image/png',
        ];
        /** @var \Magento\MediaStorage\Model\File\Uploader|MockObject $uploader */
        $uploader = $this->createMock(Uploader::class);
        $this->uploaderFactoryMock->expects($this->once())->method('create')->willReturn($uploader);
        $uploader->expects($this->once())->method('setAllowedExtensions')->with($this->allowedExtensions);
        $uploader->expects($this->once())->method('setAllowRenameFiles')->with(true);
        $this->mediaWriteDirectoryMock->expects($this->once())->method('getAbsolutePath')->with($this->baseTmpPath)
            ->willReturn($this->basePath);
        $uploader->expects($this->once())->method('save')->with($this->basePath)
            ->willReturn(['tmp_name' => $this->baseTmpPath, 'file' => $fileId, 'path' => $this->basePath]);
        $uploader->expects($this->atLeastOnce())->method('checkMimeType')->with($allowedMimeTypes)->willReturn(true);
        $storeMock = $this->createPartialMock(
            Store::class,
            ['getBaseUrl']
        );
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl');
        $this->coreFileStorageDatabaseMock->expects($this->once())->method('saveFile');

        $result = $this->imageUploader->saveFileToTmpDir($fileId);

        $this->assertArrayNotHasKey('path', $result);
    }
}
