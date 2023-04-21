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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase
{
    /**
     * @var FileTransferFactory|MockObject
     */
    protected $httpFactoryMock;
    /**
     * @var Upload
     */
    private Upload $upload;
    /**
     * @var DataHelper|MockObject
     */
    private $importExportDataMock;

    /**
     * @var UploaderFactory|MockObject
     */
    private $uploaderFactoryMock;

    /**
     * @var Random|MockObject
     */
    private $randomMock;

    /**
     * @var MockObject|MockObject
     */
    private MockObject $filesystemMock;

    /**
     * @var Http|MockObject
     */
    private Http $adapterMock;

    /**
     * @var Uploader
     */
    private Uploader $uploaderMock;
    /**
     * Test importSource() method
     */
    public function testUploadSource(): void
    {
        $this->adapterMock
            ->method('isValid')
            ->willReturn(true);
        $this->httpFactoryMock
            ->method('create')
            ->willReturn($this->adapterMock);
        $this->uploaderMock = $this->createMock(Uploader::class);
        $this->uploaderMock
            ->method('setAllowedExtensions')
            ->with(['csv', 'zip']);
        $this->uploaderMock
            ->method('skipDbProcessing')
            ->with(true);
        $this->uploaderFactoryMock
            ->method('create')
            ->with(['fileId' => 'import_file'])
            ->willReturn($this->uploaderMock);
        $this->randomMock
            ->method('getRandomString')
            ->with(32);
        $this->uploaderMock
            ->method('save')
            ->willReturn(['file' => 'testString']);
        $result = $this->upload->uploadSource('testString');
        $this->assertIsArray($result);
    }

    /**
     * Set up
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
        $directoryWrite
            ->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('dasdad');
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($directoryWrite);
        $this->upload = new Upload(
            $this->httpFactoryMock,
            $this->importExportDataMock,
            $this->uploaderFactoryMock,
            $this->randomMock,
            $this->filesystemMock
        );
    }
}
