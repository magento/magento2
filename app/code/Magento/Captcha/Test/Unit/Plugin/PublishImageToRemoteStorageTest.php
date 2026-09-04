<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Plugin;

use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Plugin\PublishImageToRemoteStorage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PublishImageToRemoteStorageTest extends TestCase
{
    private const IMAGE_ID = 'a1b2c3';

    private const LOCAL_DIR = '/var/www/pub/media/captcha/base/';

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var TargetDirectory|MockObject
     */
    private $targetDirectory;

    /**
     * @var WriteInterface|MockObject
     */
    private $localDirectory;

    /**
     * @var WriteInterface|MockObject
     */
    private $mediaDirectory;

    /**
     * @var FileDriver|MockObject
     */
    private $localDriver;

    /**
     * @var DefaultModel|MockObject
     */
    private $captcha;

    /**
     * @var PublishImageToRemoteStorage
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->targetDirectory = $this->createMock(TargetDirectory::class);
        $this->localDirectory = $this->createMock(WriteInterface::class);
        $this->mediaDirectory = $this->createMock(WriteInterface::class);
        $this->localDriver = $this->createMock(FileDriver::class);
        $this->captcha = $this->createMock(DefaultModel::class);

        $this->filesystem->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA, DriverPool::FILE)
            ->willReturn($this->localDirectory);
        $this->targetDirectory->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);
        $this->localDirectory->method('getDriver')->willReturn($this->localDriver);

        $this->captcha->method('getImgDir')->willReturn(self::LOCAL_DIR);
        $this->captcha->method('getSuffix')->willReturn('.png');

        $this->plugin = new PublishImageToRemoteStorage($this->filesystem, $this->targetDirectory);
    }

    /**
     * The image is already in the right place when the media storage is the local filesystem.
     */
    public function testImageIsNotMovedWhenRemoteStorageIsDisabled(): void
    {
        $this->mediaDirectory->method('getDriver')->willReturn($this->localDriver);
        $this->localDriver->expects($this->never())->method('rename');

        $this->assertSame(self::IMAGE_ID, $this->plugin->afterGenerate($this->captcha, self::IMAGE_ID));
    }

    /**
     * The image has to be moved to the media storage that serves the media base URL.
     */
    public function testImageIsMovedToRemoteStorage(): void
    {
        $remoteDriver = $this->createMock(DriverInterface::class);
        $this->mediaDirectory->method('getDriver')->willReturn($remoteDriver);

        $this->localDriver->method('isFile')
            ->with(self::LOCAL_DIR . self::IMAGE_ID . '.png')
            ->willReturn(true);
        $this->localDirectory->method('getRelativePath')
            ->with(self::LOCAL_DIR . self::IMAGE_ID . '.png')
            ->willReturn('captcha/base/' . self::IMAGE_ID . '.png');
        $this->mediaDirectory->method('getAbsolutePath')
            ->with('captcha/base/' . self::IMAGE_ID . '.png')
            ->willReturn('media/captcha/base/' . self::IMAGE_ID . '.png');

        $this->localDriver->expects($this->once())
            ->method('rename')
            ->with(
                self::LOCAL_DIR . self::IMAGE_ID . '.png',
                'media/captcha/base/' . self::IMAGE_ID . '.png',
                $remoteDriver
            )
            ->willReturn(true);

        $this->assertSame(self::IMAGE_ID, $this->plugin->afterGenerate($this->captcha, self::IMAGE_ID));
    }

    /**
     * Nothing to move when the image has not been rendered, e.g. when GD rendering has been suppressed.
     */
    public function testNothingIsMovedWhenImageDoesNotExist(): void
    {
        $remoteDriver = $this->createMock(DriverInterface::class);
        $this->mediaDirectory->method('getDriver')->willReturn($remoteDriver);
        $this->localDriver->method('isFile')->willReturn(false);

        $this->localDriver->expects($this->never())->method('rename');

        $this->assertSame(self::IMAGE_ID, $this->plugin->afterGenerate($this->captcha, self::IMAGE_ID));
    }
}
