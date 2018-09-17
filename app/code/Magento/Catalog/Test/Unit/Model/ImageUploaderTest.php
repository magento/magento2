<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

/**
 * Magento\Catalog\Model\ImageUploader unit tests.
 */
class ImageUploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * Core file storage database.
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
     * Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderFactoryMock;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * Base tmp path.
     *
     * @var string
     */
    private $baseTmpPath;

    /**
     * Base path.
     *
     * @var string
     */
    private $basePath;

    /**
     * Allowed extensions.
     *
     * @var string
     */
    private $allowedExtensions;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->coreFileStorageDatabaseMock = $this->getMockBuilder(
            \Magento\MediaStorage\Helper\File\Storage\Database::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaDirectoryMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaWriteDirectoryMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaDirectoryMock->expects($this->any())->method('getDirectoryWrite')->willReturn(
            $this->mediaWriteDirectoryMock
        );
        $this->uploaderFactoryMock = $this->getMockBuilder(
            \Magento\MediaStorage\Model\File\UploaderFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(
            \Magento\Store\Model\StoreManagerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->baseTmpPath = 'base/tmp/';
        $this->basePath =  'base/real/';
        $this->allowedExtensions = ['.jpg'];

        $this->imageUploader =
            new \Magento\Catalog\Model\ImageUploader(
                $this->coreFileStorageDatabaseMock,
                $this->mediaDirectoryMock,
                $this->uploaderFactoryMock,
                $this->storeManagerMock,
                $this->loggerMock,
                $this->baseTmpPath,
                $this->basePath,
                $this->allowedExtensions
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
        $uploader = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderFactoryMock->expects($this->once())->method('create')->willReturn($uploader);
        $uploader->expects($this->once())->method('setAllowedExtensions')->with($this->allowedExtensions);
        $uploader->expects($this->once())->method('setAllowRenameFiles')->with(true);
        $this->mediaWriteDirectoryMock->expects($this->once())->method('getAbsolutePath')->with($this->baseTmpPath)
            ->willReturn($this->basePath);
        $uploader->expects($this->once())->method('save')->with($this->basePath)
            ->willReturn(['tmp_name' => $this->baseTmpPath, 'file' => $fileId, 'path' => $this->basePath]);
        $uploader->expects($this->atLeastOnce())->method('checkMimeType')->with($allowedMimeTypes)->willReturn(true);
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseUrl'])
            ->getMock();

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl');
        $this->coreFileStorageDatabaseMock->expects($this->once())->method('saveFile');

        $result = $this->imageUploader->saveFileToTmpDir($fileId);

        $this->assertArrayNotHasKey('path', $result);
    }
}
