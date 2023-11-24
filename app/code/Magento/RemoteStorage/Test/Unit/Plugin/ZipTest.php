<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Test\Unit\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\RemoteStorage\Model\Config;
use Magento\RemoteStorage\Plugin\Zip;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ZipTest extends TestCase
{
    /**
     * @var Zip
     */
    private $plugin;

    /**
     * @var WriteInterface|MockObject
     */
    private $tmpDirectoryWriteMock;

    /**
     * @var WriteInterface|MockObject
     */
    private $remoteDirectoryWriteMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Framework\Archive\Zip|MockObject
     */
    private $subjectMock;

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function setUp(): void
    {
        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->createMock(Filesystem::class);
        /** @var TargetDirectory|MockObject $targetDirectory */
        $targetDirectory = $this->createMock(TargetDirectory::class);
        $this->subjectMock = $this->createMock(\Magento\Framework\Archive\Zip::class);
        $this->configMock = $this->createMock(Config::class);
        $this->tmpDirectoryWriteMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->remoteDirectoryWriteMock = $this->getMockForAbstractClass(WriteInterface::class);
        $filesystem->expects(self::once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::TMP)
            ->willReturn($this->tmpDirectoryWriteMock);
        $targetDirectory->expects(self::once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->remoteDirectoryWriteMock);

        $this->plugin = new Zip(
            $filesystem,
            $targetDirectory,
            $this->configMock
        );
    }

    public function testRemoteStorageEnabled()
    {
        $this->configMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->remoteDirectoryWriteMock->expects(self::never())
            ->method('getAbsolutePath');
        $this->tmpDirectoryWriteMock->expects(self::never())
            ->method('getAbsolutePath');

        self::assertEquals(
            '/path/to.zip',
            $this->plugin->aroundUnpack(
                $this->subjectMock,
                $this->getProceedFunction(),
                '/path/from.csv',
                '/path/to.zip'
            )
        );
    }

    public function testRemoteStorageIsNotEnabled()
    {
        $remoteDriverMock = $this->getMockForAbstractClass(DriverInterface::class);
        $tmpDriverMock = $this->getMockForAbstractClass(DriverInterface::class);
        $this->configMock->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->remoteDirectoryWriteMock->expects(self::any())
            ->method('getDriver')
            ->willReturn($remoteDriverMock);
        $this->tmpDirectoryWriteMock->expects(self::any())
            ->method('getDriver')
            ->willReturn($tmpDriverMock);
        $this->remoteDirectoryWriteMock->expects(self::once())
            ->method('getAbsolutePath')
            ->with('/remote/path/from.csv')
            ->willReturn('/remote/path/from.csv');
        $this->remoteDirectoryWriteMock->expects(self::once())
            ->method('isFile')
            ->with('/remote/path/from.csv')
            ->willReturn(true);
        $this->tmpDirectoryWriteMock->expects(self::exactly(2))
            ->method('getAbsolutePath')
            ->willReturn('/path/to/tmp/dir/');
        $remoteDriverMock->expects(self::once())
            ->method('fileGetContents')
            ->with('/remote/path/from.csv')
            ->willReturn('file content');
        $tmpDriverMock->expects(self::once())
            ->method('filePutContents')
            ->with('/path/to/tmp/dir/from.csv', 'file content')
            ->willReturn(true);
        $tmpDriverMock->expects(self::once())
            ->method('rename')
            ->with('/path/to/tmp/dir/to.zip', '/remote/path/to.zip', $remoteDriverMock);
        $this->tmpDirectoryWriteMock->expects(self::once())
            ->method('delete')
            ->with('/path/to/tmp/dir/from.csv');

        self::assertEquals(
            '/remote/path/to.zip',
            $this->plugin->aroundUnpack(
                $this->subjectMock,
                $this->getProceedFunction(),
                '/remote/path/from.csv',
                '/remote/path/to.zip'
            )
        );
    }

    /**
     * @return \Closure
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getProceedFunction()
    {
        return function ($source, $destination) {
            return $destination;
        };
    }
}
