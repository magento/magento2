<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class ImageUploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreFileStorageDatabaseMock;

    /**
     * Media directory object (writable).
     *
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaDirectoryMock;

    /**
     * Media directory object (writable).
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaWriteDirectoryMock;

    /**
     * Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderFactoryMock;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->coreFileStorageDatabaseMock = $this->createMock(
            \Magento\MediaStorage\Helper\File\Storage\Database::class
        );
        $this->mediaDirectoryMock = $this->createMock(
            \Magento\Framework\Filesystem::class
        );
        $this->mediaWriteDirectoryMock = $this->createMock(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $this->mediaDirectoryMock->expects($this->any())->method('getDirectoryWrite')->willReturn(
            $this->mediaWriteDirectoryMock
        );
        $this->uploaderFactoryMock = $this->createMock(
            \Magento\MediaStorage\Model\File\UploaderFactory::class
        );
        $this->storeManagerMock = $this->createMock(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->baseTmpPath = 'base/tmp/';
        $this->basePath =  'base/real/';
        $this->allowedExtensions = ['.jpg'];
        $this->allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'];

        $this->imageUploader =
            new \Magento\Catalog\Model\ImageUploader(
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
        /** @var \Magento\MediaStorage\Model\File\Uploader|\PHPUnit_Framework_MockObject_MockObject $uploader */
        $uploader = $this->createMock(\Magento\MediaStorage\Model\File\Uploader::class);
        $this->uploaderFactoryMock->expects($this->once())->method('create')->willReturn($uploader);
        $uploader->expects($this->once())->method('setAllowedExtensions')->with($this->allowedExtensions);
        $uploader->expects($this->once())->method('setAllowRenameFiles')->with(true);
        $this->mediaWriteDirectoryMock->expects($this->once())->method('getAbsolutePath')->with($this->baseTmpPath)
            ->willReturn($this->basePath);
        $uploader->expects($this->once())->method('save')->with($this->basePath)
            ->willReturn(['tmp_name' => $this->baseTmpPath, 'file' => $fileId, 'path' => $this->basePath]);
        $uploader->expects($this->atLeastOnce())->method('checkMimeType')->with($allowedMimeTypes)->willReturn(true);
        $storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['getBaseUrl']
        );
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl');
        $this->coreFileStorageDatabaseMock->expects($this->once())->method('saveFile');

        $result = $this->imageUploader->saveFileToTmpDir($fileId);

        $this->assertArrayNotHasKey('path', $result);
    }
}
