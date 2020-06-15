<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Helper\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Downloadable\Helper\File.
 */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $model;

    /**
     * @var Database|MockObject
     */
    private $coreFileStorageDatabaseMock;

    /**
     * @var WriteInterface|MockObject
     */
    private $mediaDirectoryMock;

    /**
     * @var DriverInterface|MockObject
     */
    private $driverMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->mediaDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        /** @var Context|MockObject $appContext */
        $appContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorageDatabaseMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->driverMock = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new File(
            $appContext,
            $this->coreFileStorageDatabaseMock,
            $filesystem,
            $this->driverMock
        );
    }

    /**
     * Test upload from tmp
     *
     * @return void
     */
    public function testUploadFromTmp(): void
    {
        $uploaderMock = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles');
        $uploaderMock->expects($this->once())
            ->method('setFilesDispersion');
        $this->mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('absPath');
        $uploaderMock->expects($this->once())
            ->method('save')
            ->with('absPath')
            ->willReturn(['file' => 'file.jpg', 'path' => 'absPath']);

        $result = $this->model->uploadFromTmp('tmpPath', $uploaderMock);

        $this->assertArrayNotHasKey('path', $result);
    }

    /**
     * Test move file from tmp
     *
     * @return void
     */
    public function testMoveFileFromTmp(): void
    {
        $basePath = 'downloadable/files/links';
        $tmpPath = 'downloadable/tmp/links';
        $filePath = '/f/i/file.pdf';
        $file[] = [
            'file' => $filePath,
            'name' => 'file.pdf',
            'status' => 'new',
        ];

        $this->driverMock->expects($this->once())
            ->method('getParentDirectory')
            ->willReturn('/f/i');

        $this->mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($basePath)
            ->willReturn('absPath');

        $this->coreFileStorageDatabaseMock->expects($this->once())
            ->method('copyFile')
            ->with($tmpPath . $filePath, $basePath . $filePath);
        $this->mediaDirectoryMock->expects($this->once())
            ->method('renameFile')
            ->with($tmpPath . $filePath, $basePath . $filePath);

        $this->model->moveFileFromTmp($tmpPath, $basePath, $file);
    }
}
