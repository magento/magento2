<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

namespace Magento\RemoteStorage\Test\Unit\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\RemoteStorage\Model\Config;
use Magento\RemoteStorage\Plugin\Image;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImageTest extends TestCase
{
    /**
     * @var File|MockObject
     */
    private $ioFile;

    /**
     * @var Image
     */
    private $plugin;

    /**
     * @var WriteInterface|MockObject
     */
    private $tmpDirectoryWrite;

    /**
     * @var WriteInterface|MockObject
     */
    private $targetDirectoryWrite;

    /**
     * @var string[]
     */
    private $tmpFileContents = [];

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function setUp(): void
    {
        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->createMock(Filesystem::class);
        $this->ioFile = $this->createMock(File::class);
        /** @var TargetDirectory|MockObject $targetDirectory */
        $targetDirectory = $this->createMock(TargetDirectory::class);
        /** @var Config|MockObject $config */
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->tmpDirectoryWrite = $this->createMock(WriteInterface::class);
        $this->targetDirectoryWrite = $this->createMock(WriteInterface::class);
        $filesystem->expects(self::atLeastOnce())->method('getDirectoryWrite')->with(DirectoryList::TMP)
            ->willReturn($this->tmpDirectoryWrite);
        $targetDirectory->expects(self::atLeastOnce())->method('getDirectoryWrite')->with(DirectoryList::ROOT)
            ->willReturn($this->targetDirectoryWrite);
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $this->plugin = new Image(
            $filesystem,
            $this->ioFile,
            $targetDirectory,
            $config,
            $logger
        );
    }

    /**
     * @param string $destination
     * @param string $newDestination
     * @param string|null $newName
     * @param string|null $oldName
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    #[DataProvider('aroundSaveDataProvider')]
    public function testAroundSaveWithNewName(
        string $destination,
        string $newDestination,
        ?string $newName,
        ?string $oldName
    ): void {
        $tmpFilePattern = '#^/tmp/[0-9a-f]{16}_save_[0-9a-f]{64}\.file$#';
        /** @var AbstractAdapter $subject */
        $subject = $this->createMock(AbstractAdapter::class);
        $savedTo = [];
        $proceed = function (string $path, ?string $name) use (&$savedTo) {
            $savedTo = [$path, $name];
        };
        $targetDriver = $this->createMock(DriverInterface::class);
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('getDriver')
            ->willReturn($targetDriver);
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('getAbsolutePath')
            ->willReturn('/tmp/');
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects(self::once())->method('rename')
            ->with(self::matchesRegularExpression($tmpFilePattern), $newDestination, $targetDriver);
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('getDriver')->willReturn($driver);
        $this->ioFile->method('getPathInfo')
            ->willReturnMap(
                [
                    [$destination . $oldName, ['dirname' => $destination, 'basename' => 'old_name.file']]
                ]
            );
        $this->plugin->aroundSave($subject, $proceed, $destination . $oldName, $newName);

        self::assertMatchesRegularExpression($tmpFilePattern, $savedTo[0]);
        self::assertNull($savedTo[1]);
    }

    /**
     * @return array
     */
    public static function aroundSaveDataProvider(): array
    {
        return [
            'with_new_name' => [
                'destination' => 'destination/',
                'newDestination' => 'destination/new_name.file',
                'newName' => 'new_name.file',
                'oldName' => null
            ],
            'with_old_name' => [
                'destination' => 'destination/',
                'newDestination' => 'destination/old_name.file',
                'newName' => null,
                'oldName' => 'old_name.file'
            ]
        ];
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testBeforeOpen(): void
    {
        /** @var AbstractAdapter $subject */
        $subject = $this->createMock(AbstractAdapter::class);
        $filename = '/path/file_name.file';
        $absolutePath = 'absolute' . $filename;
        $tmpAbsolutePath = '/var/www/magento2/tmp/';
        $tmpFilePathPattern = '#^/var/www/magento2/tmp/[0-9a-f]{16}_[0-9a-f]{64}\.file$#';
        $content = 'Just a test';

        $targetDriver = $this->createMock(DriverInterface::class);
        $targetDriver->expects(self::atLeastOnce())->method('fileGetContents')->with($filename)
            ->willReturn($content);
        $tmpDriver = $this->createMock(DriverInterface::class);
        $tmpDriver->expects(self::atLeastOnce())->method('filePutContents')
            ->with(self::matchesRegularExpression($tmpFilePathPattern), $content)
            ->willReturn(true);
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('getAbsolutePath')->with($filename)
            ->willReturn($absolutePath);
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('isFile')->with($absolutePath)
            ->willReturn(true);
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('getDriver')
            ->willReturn($targetDriver);
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('getDriver')
            ->willReturn($tmpDriver);
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('create');
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('getAbsolutePath')
            ->willReturn($tmpAbsolutePath);

        $result = $this->plugin->beforeOpen($subject, $filename);

        self::assertCount(1, $result);
        self::assertMatchesRegularExpression($tmpFilePathPattern, $result[0]);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testBeforeOpenUsesDistinctTmpFilesForSameBasename(): void
    {
        /** @var AbstractAdapter $subject */
        $subject = $this->createStub(AbstractAdapter::class);
        $this->mockRemoteCopy();

        [$first] = $this->plugin->beforeOpen($subject, 'catalog/product/a/b/image.jpg');
        [$second] = $this->plugin->beforeOpen($subject, 'catalog/product/c/d/image.jpg');
        [$firstAgain] = $this->plugin->beforeOpen($subject, 'catalog/product/a/b/image.jpg');

        self::assertNotSame($first, $second);
        self::assertStringEndsWith('.jpg', $first);
        self::assertStringEndsWith('.jpg', $second);
        self::assertSame($first, $firstAgain);
        self::assertSame('content of catalog/product/a/b/image.jpg', $this->tmpFileContents[$first]);
        self::assertSame('content of catalog/product/c/d/image.jpg', $this->tmpFileContents[$second]);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testBeforeOpenUsesDistinctTmpFilesPerInstance(): void
    {
        /** @var AbstractAdapter $subject */
        $subject = $this->createStub(AbstractAdapter::class);
        $this->mockRemoteCopy();
        $otherPlugin = $this->createOtherPlugin();

        [$first] = $this->plugin->beforeOpen($subject, 'catalog/product/a/b/image.jpg');
        [$second] = $otherPlugin->beforeOpen($subject, 'catalog/product/a/b/image.jpg');

        self::assertNotSame($first, $second);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testBeforeOpenCopiesAgainWhenCachedTmpFileIsGone(): void
    {
        /** @var AbstractAdapter $subject */
        $subject = $this->createStub(AbstractAdapter::class);
        $this->mockRemoteCopy();
        $filename = 'catalog/product/a/b/image.jpg';

        [$tmpFile] = $this->plugin->beforeOpen($subject, $filename);
        unset($this->tmpFileContents[$tmpFile]);
        [$tmpFileAgain] = $this->plugin->beforeOpen($subject, $filename);

        self::assertArrayHasKey($tmpFileAgain, $this->tmpFileContents);
        self::assertSame('content of ' . $filename, $this->tmpFileContents[$tmpFileAgain]);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testAroundSaveUsesDistinctTmpFilesPerInstanceAndRemovesThem(): void
    {
        /** @var AbstractAdapter $subject */
        $subject = $this->createStub(AbstractAdapter::class);
        $this->mockRemoteCopy();
        $movedFrom = [];
        $this->mockMoveToRemote($movedFrom);
        $destination = '/remote/pub/media/catalog/product/cache/abc/a/b/image.jpg';
        $savedTo = [];
        $proceed = function (string $path) use (&$savedTo) {
            $savedTo[] = $path;
            $this->tmpFileContents[$path] = 'resized';
        };

        $this->plugin->aroundSave($subject, $proceed, dirname($destination), 'image.jpg');
        $this->createOtherPlugin()->aroundSave($subject, $proceed, $destination);

        self::assertCount(2, $savedTo);
        self::assertNotSame($savedTo[0], $savedTo[1]);
        self::assertStringStartsWith('/var/tmp/', $savedTo[0]);
        self::assertStringEndsWith('.jpg', $savedTo[0]);
        self::assertSame([$savedTo[0] => $destination, $savedTo[1] => $destination], $movedFrom);
        self::assertSame([], $this->tmpFileContents);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testAroundSaveRemovesTmpFileWhenMoveFails(): void
    {
        /** @var AbstractAdapter $subject */
        $subject = $this->createStub(AbstractAdapter::class);
        $this->mockRemoteCopy();
        $this->tmpDirectoryWrite->getDriver()->method('rename')
            ->willThrowException(new FileSystemException(__('Upload failed')));
        $this->tmpDirectoryWrite->method('delete')
            ->willReturnCallback(function (string $path): bool {
                unset($this->tmpFileContents[$path]);
                return true;
            });
        $proceed = function (string $path) {
            $this->tmpFileContents[$path] = 'resized';
        };

        try {
            $this->plugin->aroundSave($subject, $proceed, '/remote/pub/media', 'image.jpg');
            self::fail('The move failure must not be swallowed');
        } catch (FileSystemException $e) {
            self::assertSame('Upload failed', $e->getMessage());
        }
        self::assertSame([], $this->tmpFileContents);
    }

    /**
     * Wire the tmp driver to move a tmp file to remote storage and drop it, as the local driver does
     *
     * @param array $movedFrom
     * @return void
     */
    private function mockMoveToRemote(array &$movedFrom): void
    {
        $this->ioFile->method('getPathInfo')
            ->willReturnCallback(static fn (string $path): array => [
                'dirname' => dirname($path),
                'basename' => basename($path),
            ]);
        $this->tmpDirectoryWrite->getDriver()->method('rename')
            ->willReturnCallback(function (string $from, string $to) use (&$movedFrom): bool {
                $movedFrom[$from] = $to;
                unset($this->tmpFileContents[$from]);
                return true;
            });
    }

    /**
     * @return Image
     */
    private function createOtherPlugin(): Image
    {
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($this->tmpDirectoryWrite);
        $targetDirectory = $this->createStub(TargetDirectory::class);
        $targetDirectory->method('getDirectoryWrite')->willReturn($this->targetDirectoryWrite);
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn(true);

        return new Image(
            $filesystem,
            $this->ioFile,
            $targetDirectory,
            $config,
            $this->createStub(LoggerInterface::class)
        );
    }

    /**
     * Wire the directory mocks to copy "content of <path>" into an in-memory tmp directory
     *
     * @return void
     */
    private function mockRemoteCopy(): void
    {
        $targetDriver = $this->createStub(DriverInterface::class);
        $targetDriver->method('fileGetContents')
            ->willReturnCallback(static fn (string $path): string => 'content of ' . $path);
        $tmpDriver = $this->createStub(DriverInterface::class);
        $tmpDriver->method('filePutContents')
            ->willReturnCallback(function (string $path, string $content): int {
                $this->tmpFileContents[$path] = $content;
                return strlen($content);
            });
        $this->targetDirectoryWrite->method('getAbsolutePath')
            ->willReturnCallback(static fn (string $path): string => '/remote/' . $path);
        $this->targetDirectoryWrite->method('isFile')->willReturn(true);
        $this->targetDirectoryWrite->method('getDriver')->willReturn($targetDriver);
        $this->tmpDirectoryWrite->method('getAbsolutePath')->willReturn('/var/tmp/');
        $this->tmpDirectoryWrite->method('getDriver')->willReturn($tmpDriver);
        $this->tmpDirectoryWrite->method('isFile')
            ->willReturnCallback(fn (string $path): bool => isset($this->tmpFileContents[$path]));
    }
}
