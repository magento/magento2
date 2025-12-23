<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

namespace Magento\RemoteStorage\Test\Unit\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
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
        $tmpDestination = '/tmp/' . $destination;
        /** @var AbstractAdapter $subject */
        $subject = $this->createMock(AbstractAdapter::class);
        $proceed = function () {
        };
        $targetDriver = $this->createMock(DriverInterface::class);
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('getRelativePath')
            ->willReturn($destination . $oldName);
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('getDriver')
            ->willReturn($targetDriver);
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('getAbsolutePath')
            ->willReturn($tmpDestination);
        $driver = $this->createMock(DriverInterface::class);
        $actualName = $newName ?? $oldName;
        $driver->expects(self::atLeastOnce())->method('rename')
            ->with($tmpDestination . $actualName, $newDestination, $driver);
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('getDriver')->willReturn($driver);
        $this->ioFile->method('getPathInfo')
            ->willReturnMap(
                [
                    [$tmpDestination, ['dirname' => $tmpDestination, 'basename' => 'old_name.file']],
                    [$destination . $oldName, ['dirname' => $destination, 'basename' => 'old_name.file']]
                ]
            );
        $this->plugin->aroundSave($subject, $proceed, $destination . $oldName, $newName);
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
        $tmpAbsolutePath = '/var/www/magento2/tmp';
        $tmpFilePath = $tmpAbsolutePath . 'file_name.file';
        $content = 'Just a test';

        $targetDriver = $this->createMock(DriverInterface::class);
        $targetDriver->expects(self::atLeastOnce())->method('fileGetContents')->with($filename)
            ->willReturn($content);
        $tmpDriver = $this->createMock(DriverInterface::class);
        $tmpDriver->expects(self::atLeastOnce())->method('filePutContents')->with($tmpFilePath, $content)
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

        self::assertEquals([$tmpFilePath], $this->plugin->beforeOpen($subject, $filename));
    }
}
