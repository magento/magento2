<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
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
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->fileInfoMock = $this->createMock(FileInfo::class);

        $this->directoryMock = $this->getMockForAbstractClass(WriteInterface::class);

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
     * @dataProvider recordNewFileDataProvider
     */
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
                ->withConsecutive(
                    [$pathToExistingFile],
                    [$directoryName]
                );
        }

        $this->assertTrue($this->fileRecorder->recordNewFile($this->encodedContextMock));
    }

    /**
     * @return array
     */
    public function recordNewFileDataProvider()
    {
        return [
            'File doesn\'t exist' => [''],
            'Existing file into subdirectory' => ['dir_name/file.txt'],
            'Existing file doesn\'t into subdirectory' => ['file.txt'],
        ];
    }
}
