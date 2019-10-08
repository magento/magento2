<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

class ExportDataHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var Archive|\PHPUnit_Framework_MockObject_MockObject
     */
    private $archiveMock;

    /**
     * @var ReportWriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reportWriterMock;

    /**
     * @var Cryptographer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cryptographerMock;

    /**
     * @var FileRecorder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileRecorderMock;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryMock;

    /**
     * @var EncodedContext|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->archiveMock = $this->getMockBuilder(Archive::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportWriterMock = $this->getMockBuilder(ReportWriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cryptographerMock = $this->getMockBuilder(Cryptographer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileRecorderMock = $this->getMockBuilder(FileRecorder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->encodedContextMock = $this->getMockBuilder(EncodedContext::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->with(DirectoryList::VAR_DIR)
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testPrepareExportDataWithLocalizedException()
    {
        $tmpFilesDirectoryPath = $this->subdirectoryPath . 'tmp/';
        $archivePath = $this->subdirectoryPath . $this->archiveName;

        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
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
