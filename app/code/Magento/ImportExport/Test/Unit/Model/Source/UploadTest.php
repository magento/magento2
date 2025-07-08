<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Source;

use Magento\Framework\File\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Math\Random;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Source\Upload;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase
{
    /**
     * @var Upload
     */
    private Upload $upload;

    /**
     * @var FileTransferFactory
     */
    protected FileTransferFactory $httpFactoryMock;

    /**
     * @var DataHelper
     */
    private DataHelper $importExportDataMock;

    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactoryMock;

    /**
     * @var Random
     */
    private Random $randomMock;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystemMock;

    /**
     * @var Http
     */
    private Http $adapterMock;

    /**
     * @var Uploader
     */
    private Uploader $uploaderMock;

    protected function setUp(): void
    {
        $directoryAbsolutePath = 'importexport/';
        $this->httpFactoryMock = $this->createPartialMock(FileTransferFactory::class, ['create']);
        $this->importExportDataMock = $this->createMock(DataHelper::class);
        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->randomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->adapterMock = $this->createMock(Http::class);
        $directoryWriteMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryWriteMock->expects($this->once())->method('getAbsolutePath')->willReturn($directoryAbsolutePath);
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->willReturn($directoryWriteMock);
        $this->upload = new Upload(
            $this->httpFactoryMock,
            $this->importExportDataMock,
            $this->uploaderFactoryMock,
            $this->randomMock,
            $this->filesystemMock
        );
    }

    /**
     * @return void
     */
    public function testValidateFileUploadReturnsSavedFileArray(): void
    {
        $allowedExtensions = ['csv', 'zip'];
        $savedFileName = 'testString';
        $importFileId = 'import_file';
        $randomStringLength=32;
        $this->adapterMock->method('isValid')->willReturn(true);
        $this->httpFactoryMock->method('create')->willReturn($this->adapterMock);
        $this->uploaderMock = $this->createMock(Uploader::class);
        $this->uploaderMock->method('setAllowedExtensions')->with($allowedExtensions);
        $this->uploaderMock->method('skipDbProcessing')->with(true);
        $this->uploaderFactoryMock->method('create')
            ->with(['fileId' => $importFileId])
            ->willReturn($this->uploaderMock);
        $this->randomMock->method('getRandomString')->with($randomStringLength);
        $this->uploaderMock->method('save')->willReturn(['file' => $savedFileName]);
        $result = $this->upload->uploadSource($savedFileName);
        $this->assertIsArray($result);
        $this->assertEquals($savedFileName, $result['file']);
    }
}
