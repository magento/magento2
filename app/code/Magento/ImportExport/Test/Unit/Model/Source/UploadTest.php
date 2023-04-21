<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Source;

use Laminas\File\Transfer\Adapter\Http;
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
     * @var FileTransferFactory|MockObject
     */
    protected FileTransferFactory|MockObject $httpFactoryMock;

    /**
     * @var DataHelper|MockObject
     */
    private DataHelper|MockObject $importExportDataMock;

    /**
     * @var UploaderFactory|MockObject
     */
    private UploaderFactory|MockObject $uploaderFactoryMock;

    /**
     * @var Random|MockObject
     */
    private Random|MockObject $randomMock;

    /**
     * @var Filesystem|MockObject
     */
    protected Filesystem|MockObject $filesystemMock;

    /**
     * @var Http|MockObject
     */
    private Http|MockObject $adapterMock;

    /**
     * @var Uploader|MockObject
     */
    private Uploader|MockObject $uploaderMock;

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
