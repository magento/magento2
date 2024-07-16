<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\MediaStorage\Test\Unit\App;

use Exception;
use LogicException;
use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Catalog\Model\View\Asset\Placeholder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\MediaStorage\App\Media;
use Magento\MediaStorage\Model\File\Storage\Config;
use Magento\MediaStorage\Model\File\Storage\ConfigFactory;
use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;
use Magento\MediaStorage\Service\ImageResize;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verification for Media class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MediaTest extends TestCase
{
    public const MEDIA_DIRECTORY = 'mediaDirectory';
    public const RELATIVE_FILE_PATH = 'test/file.png';
    public const CACHE_FILE_PATH = 'var';

    /**
     * @var Media
     */
    private $mediaModel;

    /**
     * @var ConfigFactory|MockObject
     */
    private $configFactoryMock;

    /**
     * @var SynchronizationFactory|MockObject
     */
    private $syncFactoryMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Synchronization|MockObject
     */
    private $sync;

    /**
     * @var Response|MockObject
     */
    private $responseMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var Read|MockObject
     */
    private $directoryMediaMock;

    /**
     * @var Read|MockObject
     */
    private $directoryPubMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->sync = $this->createMock(Synchronization::class);
        $this->configFactoryMock = $this->createPartialMock(ConfigFactory::class, ['create']);
        $this->responseMock = $this->createMock(Response::class);
        $this->syncFactoryMock = $this->createPartialMock(SynchronizationFactory::class, ['create']);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->directoryPubMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->directoryMediaMock = $this->getMockForAbstractClass(WriteInterface::class);

        $this->configFactoryMock->method('create')
            ->willReturn($this->configMock);
        $this->syncFactoryMock->method('create')
            ->willReturn($this->sync);
        $this->filesystemMock->method('getDirectoryWrite')
            ->willReturnMap([
                [DirectoryList::PUB, DriverPool::FILE, $this->directoryPubMock],
                [DirectoryList::MEDIA, DriverPool::FILE, $this->directoryMediaMock],
            ]);
    }

    public function testProcessRequestCreatesConfigFileMediaDirectoryIsNotProvided(): void
    {
        $filePath = '/absolute/path/to/test/file.png';
        $this->directoryMediaMock->expects(self::once())
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn(self::MEDIA_DIRECTORY);
        $this->directoryPubMock->expects(self::exactly(2))
            ->method('getAbsolutePath')
            ->with(self::RELATIVE_FILE_PATH)
            ->willReturn($filePath);
        $this->configMock->expects(self::once())
            ->method('save');
        $this->sync->expects(self::once())
            ->method('synchronize')
            ->with(self::RELATIVE_FILE_PATH);
        $this->directoryPubMock->expects(self::exactly(2))
            ->method('isReadable')
            ->with(self::RELATIVE_FILE_PATH)
            ->willReturn(true);
        $this->responseMock->expects(self::once())
            ->method('setFilePath')
            ->with($filePath);
        $this->configMock->expects($this->once())
            ->method('getMediaDirectory')
            ->willReturn('');

        $this->createMediaModel()->launch();
    }

    public function testProcessRequestReturnsFileIfItsProperlySynchronized(): void
    {
        $this->mediaModel = $this->createMediaModel();

        $filePath = '/absolute/path/to/test/file.png';
        $this->sync->expects(self::once())
            ->method('synchronize')
            ->with(self::RELATIVE_FILE_PATH);
        $this->directoryMediaMock->expects(self::once())
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn(self::MEDIA_DIRECTORY);
        $this->directoryPubMock->expects(self::exactly(2))
            ->method('isReadable')
            ->with(self::RELATIVE_FILE_PATH)
            ->willReturn(true);
        $this->directoryPubMock->expects(self::exactly(2))
            ->method('getAbsolutePath')
            ->with(self::RELATIVE_FILE_PATH)
            ->willReturn($filePath);
        $this->responseMock->expects(self::once())
            ->method('setFilePath')
            ->with($filePath);
        $this->configMock->expects($this->once())
            ->method('getMediaDirectory')
            ->willReturn('');

        self::assertSame($this->responseMock, $this->mediaModel->launch());
    }

    public function testProcessRequestReturnsNotFoundIfFileIsNotSynchronized(): void
    {
        $this->mediaModel = $this->createMediaModel();

        $this->sync->expects(self::once())
            ->method('synchronize')
            ->with(self::RELATIVE_FILE_PATH);
        $this->directoryMediaMock->expects(self::once())
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn(self::MEDIA_DIRECTORY);
        $this->directoryPubMock->expects(self::exactly(2))
            ->method('isReadable')
            ->with(self::RELATIVE_FILE_PATH)
            ->willReturn(false);
        $this->configMock->expects($this->once())
            ->method('getMediaDirectory')
            ->willReturn('');
        $this->directoryPubMock->method('getAbsolutePath')->willReturn('');

        self::assertSame($this->responseMock, $this->mediaModel->launch());
    }

    /**
     * @param bool $isDeveloper
     * @param int $setBodyCalls
     *
     * @dataProvider catchExceptionDataProvider
     */
    public function testCatchException(bool $isDeveloper, int $setBodyCalls): void
    {
        /** @var Bootstrap|MockObject $bootstrap */
        $bootstrap = $this->createMock(Bootstrap::class);

        /** @var Exception|MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->responseMock->expects(self::once())
            ->method('setHttpResponseCode')
            ->with(404);
        $bootstrap->expects(self::once())
            ->method('isDeveloperMode')
            ->willReturn($isDeveloper);
        $this->responseMock->expects(self::exactly($setBodyCalls))
            ->method('setBody');
        $this->responseMock->expects(self::once())
            ->method('sendResponse');

        $this->createMediaModel()->catchException($bootstrap, $exception);
    }

    public function testExceptionWhenIsAllowedReturnsFalse(): void
    {
        $filePath = '/absolute/path/to/test/file.png';
        $this->directoryMediaMock->expects(self::once())
            ->method('getAbsolutePath')
            ->with(null)
            ->willReturn(self::MEDIA_DIRECTORY);
        $this->directoryPubMock->expects(self::once())
            ->method('getAbsolutePath')
            ->with(self::RELATIVE_FILE_PATH)
            ->willReturn($filePath);
        $this->configMock->expects(self::once())
            ->method('save');
        $this->configMock->expects($this->once())
            ->method('getMediaDirectory')
            ->willReturn('');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path is not allowed: ' . self::RELATIVE_FILE_PATH);

        $this->createMediaModel(false)->launch();
    }

    /**
     * @return array
     */
    public function catchExceptionDataProvider(): array
    {
        return [
            'default mode' => [false, 0],
            'developer mode' => [true, 1],
        ];
    }

    /**
     * Generates Media class instance for test
     *
     * @param bool $isAllowed
     * @return Media
     */
    protected function createMediaModel(bool $isAllowed = true): Media
    {
        $isAllowedCallback = function () use ($isAllowed) {
            return $isAllowed;
        };

        $driverFile =  $this->createMock(Filesystem\Driver\File::class);
        $driverFile->method('getRealPath')->willReturn('');
        $placeholderFactory = $this->createMock(PlaceholderFactory::class);
        $placeholderFactory->method('create')
            ->willReturn($this->createMock(Placeholder::class));

        return new Media(
            $this->configFactoryMock,
            $this->syncFactoryMock,
            $this->responseMock,
            $isAllowedCallback,
            self::MEDIA_DIRECTORY,
            self::CACHE_FILE_PATH,
            self::RELATIVE_FILE_PATH,
            $this->filesystemMock,
            $placeholderFactory,
            $this->createMock(State::class),
            $this->createMock(ImageResize::class),
            $driverFile,
            $this->createMock(CatalogMediaConfig::class)
        );
    }
}
