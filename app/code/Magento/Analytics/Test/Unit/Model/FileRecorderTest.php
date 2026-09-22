<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\EncodedContext;
use Magento\Analytics\Model\FileInfo;
use Magento\Analytics\Model\FileInfoFactory;
use Magento\Analytics\Model\FileInfoManager;
use Magento\Analytics\Model\FileRecorder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FileRecorderTest extends TestCase
{
    /**
     * @var FileInfoManager|MockObject
     */
    private $fileInfoManagerMock;

    /**
     * @var FileInfoFactory|MockObject
     */
    private $fileInfoFactoryMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var FileInfo|MockObject
     */
    private $fileInfoMock;

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
     * @var FileRecorder
     */
    private $fileRecorder;

    /**
     * @var string
     */
    private $fileSubdirectoryPath = 'analytics_subdir/';

    /**
     * @var string
     */
    private $encodedFileName = 'filename.tgz';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->fileInfoManagerMock = $this->createMock(FileInfoManager::class);

        $this->fileInfoFactoryMock = $this->getMockBuilder(FileInfoFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->fileInfoMock = $this->createMock(FileInfo::class);

        $this->directoryMock = $this->createMock(WriteInterface::class);

        $this->encodedContextMock = $this->createMock(EncodedContext::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->fileRecorder = $this->objectManagerHelper->getObject(
            FileRecorder::class,
            [
                'fileInfoManager' => $this->fileInfoManagerMock,
                'fileInfoFactory' => $this->fileInfoFactoryMock,
                'filesystem' => $this->filesystemMock,
                'fileSubdirectoryPath' => $this->fileSubdirectoryPath,
                'encodedFileName' => $this->encodedFileName,
            ]
        );
    }

    /**
     * @param string $pathToExistingFile
     */
    #[DataProvider('recordNewFileDataProvider')]
    public function testRecordNewFile($pathToExistingFile)
    {
        $content = openssl_random_pseudo_bytes(200);

        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->encodedContextMock
            ->expects($this->once())
            ->method('getContent')
            ->with()
            ->willReturn($content);

        $hashLength = 64;
        $fileRelativePathPattern = '#' . preg_quote($this->fileSubdirectoryPath, '#')
            . '.{' . $hashLength . '}/' . preg_quote($this->encodedFileName, '#') . '#';
        $this->directoryMock
            ->expects($this->once())
            ->method('writeFile')
            ->with($this->matchesRegularExpression($fileRelativePathPattern), $content)
            ->willReturn($this->directoryMock);

        $this->fileInfoManagerMock
            ->expects($this->once())
            ->method('load')
            ->with()
            ->willReturn($this->fileInfoMock);

        $this->encodedContextMock
            ->expects($this->once())
            ->method('getInitializationVector')
            ->with()
            ->willReturn('init_vector***');

        /** register file */
        $this->fileInfoFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(
                function ($parameters) {
                    return !empty($parameters['path']) && ('init_vector***' === $parameters['initializationVector']);
                }
            ))
            ->willReturn($this->fileInfoMock);
        $this->fileInfoManagerMock
            ->expects($this->once())
            ->method('save')
            ->with($this->fileInfoMock);

        /** remove old file */
        $this->fileInfoMock
            ->expects($this->exactly($pathToExistingFile ? 3 : 1))
            ->method('getPath')
            ->with()
            ->willReturn($pathToExistingFile);
        $directoryName = dirname($pathToExistingFile);
        if ($directoryName === '.') {
            $this->directoryMock
                ->expects($this->once())
                ->method('delete')
                ->with($pathToExistingFile);
        } elseif ($directoryName) {
            $this->directoryMock
                ->expects($this->exactly(2))
                ->method('delete')
                ->willReturnCallback(function ($arg) use ($pathToExistingFile, $directoryName) {
                    if ($arg == $pathToExistingFile || $arg == $directoryName) {
                        return null;
                    }
                });
        }

        $this->assertTrue($this->fileRecorder->recordNewFile($this->encodedContextMock));
    }

    /**
     * @return array
     */
    public static function recordNewFileDataProvider()
    {
        return [
            'File doesn\'t exist' => [''],
            'Existing file into subdirectory' => ['dir_name/file.txt'],
            'Existing file doesn\'t into subdirectory' => ['file.txt'],
        ];
    }

    /**
     * @return void
     */
    public function testRecordNewFileStreamed()
    {
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $hashLength = 64;
        $fileRelativePathPattern = '#' . preg_quote($this->fileSubdirectoryPath, '#')
            . '.{' . $hashLength . '}/' . preg_quote($this->encodedFileName, '#') . '#';

        $this->directoryMock
            ->expects($this->once())
            ->method('create');

        $fileMock = $this->createMock(FileWriteInterface::class);
        $this->directoryMock
            ->expects($this->once())
            ->method('openFile')
            ->with($this->matchesRegularExpression($fileRelativePathPattern), 'w+')
            ->willReturn($fileMock);
        $fileMock
            ->expects($this->once())
            ->method('close');

        $this->fileInfoManagerMock
            ->expects($this->once())
            ->method('load')
            ->with()
            ->willReturn($this->fileInfoMock);

        $this->encodedContextMock
            ->expects($this->once())
            ->method('getInitializationVector')
            ->with()
            ->willReturn('init_vector***');

        $this->fileInfoFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(
                function ($parameters) {
                    return !empty($parameters['path']) && ('init_vector***' === $parameters['initializationVector']);
                }
            ))
            ->willReturn($this->fileInfoMock);
        $this->fileInfoManagerMock
            ->expects($this->once())
            ->method('save')
            ->with($this->fileInfoMock);

        $this->fileInfoMock
            ->expects($this->once())
            ->method('getPath')
            ->with()
            ->willReturn('');

        $writerCalledWith = null;
        $result = $this->fileRecorder->recordNewFileStreamed(
            function ($file) use (&$writerCalledWith) {
                $writerCalledWith = $file;
                return $this->encodedContextMock;
            }
        );

        $this->assertSame($fileMock, $writerCalledWith);
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testRecordNewFileStreamedRemovesPartialFileOnFailure()
    {
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->directoryMock
            ->expects($this->once())
            ->method('create');

        $fileMock = $this->createMock(FileWriteInterface::class);
        $this->directoryMock
            ->expects($this->once())
            ->method('openFile')
            ->willReturn($fileMock);

        // The handle is closed and the partial file plus its directory are deleted before re-throwing.
        $fileMock
            ->expects($this->once())
            ->method('close');
        $this->directoryMock
            ->expects($this->exactly(2))
            ->method('delete');

        // No registration or old-file handling happens on the failure path.
        $this->fileInfoManagerMock
            ->expects($this->never())
            ->method('load');
        $this->fileInfoManagerMock
            ->expects($this->never())
            ->method('save');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('encryption failed');

        $this->fileRecorder->recordNewFileStreamed(
            function () {
                throw new \RuntimeException('encryption failed');
            }
        );
    }
}
