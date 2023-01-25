<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Cryptographer;
use Magento\Analytics\Model\EncodedContext;
use Magento\Analytics\Model\ExportDataHandler;
use Magento\Analytics\Model\FileRecorder;
use Magento\Analytics\Model\ReportWriterInterface;
use Magento\Framework\Archive;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportDataHandlerTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var Archive|MockObject
     */
    private $archiveMock;

    /**
     * @var ReportWriterInterface|MockObject
     */
    private $reportWriterMock;

    /**
     * @var Cryptographer|MockObject
     */
    private $cryptographerMock;

    /**
     * @var FileRecorder|MockObject
     */
    private $fileRecorderMock;

    /**
     * @var WriteInterface|MockObject
     */
    private $directoryMock;

    /**
     * @var EncodedContext|MockObject
     */
    private $encodedContextMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ExportDataHandler
     */
    private $exportDataHandler;

    /**
     * @var string
     */
    private $subdirectoryPath = 'analytics/';

    /**
     * @var string
     */
    private $archiveName = 'data.tgz';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->archiveMock = $this->createMock(Archive::class);

        $this->reportWriterMock = $this->getMockForAbstractClass(ReportWriterInterface::class);

        $this->cryptographerMock = $this->createMock(Cryptographer::class);

        $this->fileRecorderMock = $this->createMock(FileRecorder::class);

        $this->directoryMock = $this->getMockForAbstractClass(WriteInterface::class);

        $this->encodedContextMock = $this->createMock(EncodedContext::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->exportDataHandler = $this->objectManagerHelper->getObject(
            ExportDataHandler::class,
            [
                'filesystem' => $this->filesystemMock,
                'archive' => $this->archiveMock,
                'reportWriter' => $this->reportWriterMock,
                'cryptographer' => $this->cryptographerMock,
                'fileRecorder' => $this->fileRecorderMock,
                'subdirectoryPath' => $this->subdirectoryPath,
                'archiveName' => $this->archiveName,
            ]
        );
    }

    /**
     * @param bool $isArchiveSourceDirectory
     * @dataProvider prepareExportDataDataProvider
     */
    public function testPrepareExportData($isArchiveSourceDirectory)
    {
        $tmpFilesDirectoryPath = $this->subdirectoryPath . 'tmp/';
        $archiveRelativePath = $this->subdirectoryPath . $this->archiveName;

        $archiveSource = $isArchiveSourceDirectory ? (__DIR__) : '/tmp/' . $tmpFilesDirectoryPath;
        $archiveAbsolutePath = '/tmp/' . $archiveRelativePath;

        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::TMP)
            ->willReturn($this->directoryMock);
        $this->directoryMock
            ->expects($this->exactly(4))
            ->method('delete')
            ->withConsecutive(
                [$tmpFilesDirectoryPath],
                [$archiveRelativePath]
            );

        $this->directoryMock
            ->expects($this->exactly(4))
            ->method('getAbsolutePath')
            ->withConsecutive(
                [$tmpFilesDirectoryPath],
                [$tmpFilesDirectoryPath],
                [$archiveRelativePath],
                [$archiveRelativePath]
            )
            ->willReturnOnConsecutiveCalls(
                $archiveSource,
                $archiveSource,
                $archiveAbsolutePath,
                $archiveAbsolutePath
            );

        $this->reportWriterMock
            ->expects($this->once())
            ->method('write')
            ->with($this->directoryMock, $tmpFilesDirectoryPath);

        $this->directoryMock
            ->expects($this->exactly(2))
            ->method('isExist')
            ->withConsecutive(
                [$tmpFilesDirectoryPath],
                [$archiveRelativePath]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $this->directoryMock
            ->expects($this->once())
            ->method('create')
            ->with(dirname($archiveRelativePath));

        $this->archiveMock
            ->expects($this->once())
            ->method('pack')
            ->with(
                $archiveSource,
                $archiveAbsolutePath,
                $isArchiveSourceDirectory
            );

        $fileContent = 'Some text';
        $this->directoryMock
            ->expects($this->once())
            ->method('readFile')
            ->with($archiveRelativePath)
            ->willReturn($fileContent);

        $this->cryptographerMock
            ->expects($this->once())
            ->method('encode')
            ->with($fileContent)
            ->willReturn($this->encodedContextMock);

        $this->fileRecorderMock
            ->expects($this->once())
            ->method('recordNewFile')
            ->with($this->encodedContextMock);

        $this->assertTrue($this->exportDataHandler->prepareExportData());
    }

    /**
     * @return array
     */
    public function prepareExportDataDataProvider()
    {
        return [
            'Data source for archive is directory' => [true],
            'Data source for archive isn\'t directory' => [false],
        ];
    }

    /**
     * @return void
     */
    public function testPrepareExportDataWithLocalizedException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $tmpFilesDirectoryPath = $this->subdirectoryPath . 'tmp/';
        $archivePath = $this->subdirectoryPath . $this->archiveName;

        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::TMP)
            ->willReturn($this->directoryMock);
        $this->reportWriterMock
            ->expects($this->once())
            ->method('write')
            ->with($this->directoryMock, $tmpFilesDirectoryPath);
        $this->directoryMock
            ->expects($this->exactly(3))
            ->method('delete')
            ->withConsecutive(
                [$tmpFilesDirectoryPath],
                [$tmpFilesDirectoryPath],
                [$archivePath]
            );
        $this->directoryMock
            ->expects($this->exactly(2))
            ->method('getAbsolutePath')
            ->with($tmpFilesDirectoryPath);
        $this->directoryMock
            ->expects($this->once())
            ->method('isExist')
            ->with($tmpFilesDirectoryPath)
            ->willReturn(false);

        $this->assertNull($this->exportDataHandler->prepareExportData());
    }
}
