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
     * Directory paths sample
     */
    const DIRECTORY_ABSOLUTE_PATH = 'dasdad';
    /**
     * Allowed Extensions to Upload a file
     */
    const ALLOWED_EXTENSIONS = ['csv', 'zip'];
    /**
     * The name to use when saving the uploaded file
     */
    const SAVED_FILE_NAME = 'testString';
    /**
     * The ID of the file being imported.
     */
    const IMPORT_FILE_ID = 'import_file';

    /**
     * @var Upload
     */
    private Upload $upload;

    /**
     * @var FileTransferFactory|MockObject
     */
    protected FileTransferFactory|MockObject $httpFactoryMock;

    /**
     * @var DataHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private DataHelper|MockObject $importExportDataMock;

    /**
     * @var UploaderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private UploaderFactory|MockObject $uploaderFactoryMock;

    /**
     * @var Random|\PHPUnit\Framework\MockObject\MockObject
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
     * @var Uploader
     */
    private Uploader $uploaderMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->httpFactoryMock = $this->createPartialMock(
            FileTransferFactory::class,
            ['create']
        );
        $this->importExportDataMock = $this->createMock(DataHelper::class);
        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->randomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->adapterMock = $this->createMock(Http::class);
        $directoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryWrite->expects($this->once())->method('getAbsolutePath')
            ->willReturn(self::DIRECTORY_ABSOLUTE_PATH);
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')
            ->willReturn($directoryWrite);
        $this->upload = new Upload(
            $this->httpFactoryMock,
            $this->importExportDataMock,
            $this->uploaderFactoryMock,
            $this->randomMock,
            $this->filesystemMock
        );
    }

    /**
     * Test that the uploadSource method uploads a file and returns an array.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testUploadSource(): void
    {
        $this->adapterMock->method('isValid')->willReturn(true);
        $this->httpFactoryMock->method('create')->willReturn($this->adapterMock);
        $this->uploaderMock = $this->createMock(Uploader::class);
        $this->uploaderMock->method('setAllowedExtensions')->with(self::ALLOWED_EXTENSIONS);
        $this->uploaderMock->method('skipDbProcessing')->with(true);
        $this->uploaderFactoryMock->method('create')
            ->with(['fileId' => self::IMPORT_FILE_ID])
            ->willReturn($this->uploaderMock);
        $this->randomMock->method('getRandomString')->with(32);
        $this->uploaderMock->method('save')->willReturn(['file' => self::SAVED_FILE_NAME]);
        $result = $this->upload->uploadSource(self::SAVED_FILE_NAME);
        $this->assertIsArray($result);
    }
}
