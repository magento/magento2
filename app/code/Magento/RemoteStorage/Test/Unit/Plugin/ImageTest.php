<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $this->ioFile = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        /** @var TargetDirectory|MockObject $targetDirectory */
        $targetDirectory = $this->getMockBuilder(TargetDirectory::class)->disableOriginalConstructor()->getMock();
        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $config->expects(self::atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->tmpDirectoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->targetDirectoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $filesystem->expects(self::atLeastOnce())->method('getDirectoryWrite')->with(DirectoryList::TMP)
            ->willReturn($this->tmpDirectoryWrite);
        $targetDirectory->expects(self::atLeastOnce())->method('getDirectoryWrite')->with(DirectoryList::ROOT)
            ->willReturn($this->targetDirectoryWrite);
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->plugin = new Image(
            $filesystem,
            $this->ioFile,
            $targetDirectory,
            $config,
            $logger
        );
    }

    /**
     * @dataProvider aroundSaveDataProvider
     * @param string $destination
     * @param string $newDestination
     * @param string|null $newName
     * @param string|null $oldName
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testAroundSaveWithNewName(
        string $destination,
        string $newDestination,
        ?string $newName,
        ?string $oldName
    ): void {
        $tmpDestination = '/tmp/' . $destination;
        /** @var AbstractAdapter $subject */
        $subject = $this->getMockBuilder(AbstractAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () {
        };
        $targetDriver = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('getRelativePath')
            ->willReturn($destination . $oldName);
        $this->targetDirectoryWrite->expects(self::atLeastOnce())->method('getDriver')
            ->willReturn($targetDriver);
        $this->tmpDirectoryWrite->expects(self::atLeastOnce())->method('getAbsolutePath')
            ->willReturn($tmpDestination);
        $driver = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $subject = $this->getMockBuilder(AbstractAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filename = '/path/file_name.file';
        $absolutePath = 'absolute' . $filename;
        $tmpAbsolutePath = '/var/www/magento2/tmp';
        $tmpFilePath = $tmpAbsolutePath . 'file_name.file';
        $content = 'Just a test';

        $targetDriver = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $targetDriver->expects(self::atLeastOnce())->method('fileGetContents')->with($filename)
            ->willReturn($content);
        $tmpDriver = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
