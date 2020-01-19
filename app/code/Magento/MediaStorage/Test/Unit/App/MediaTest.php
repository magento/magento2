<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaStorage\Test\Unit\App;

use Exception;
use LogicException;
use Magento\Catalog\Model\View\Asset\Placeholder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\App\Media;
use Magento\MediaStorage\Model\File\Storage\Config;
use Magento\MediaStorage\Model\File\Storage\ConfigFactory;
use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Verification for Media class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MediaTest extends TestCase
{
    const MEDIA_DIRECTORY = 'mediaDirectory';
    const RELATIVE_FILE_PATH = 'test/file.png';
    const CACHE_FILE_PATH = 'var';

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
    private $directoryMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->sync = $this->createMock(Synchronization::class);
        $this->configFactoryMock = $this->createPartialMock(
            ConfigFactory::class,
            ['create']
        );
        $this->configFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->configMock));
        $this->syncFactoryMock = $this->createPartialMock(
            SynchronizationFactory::class,
            ['create']
        );
        $this->syncFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->sync));

        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->directoryMock = $this->getMockForAbstractClass(WriteInterface::class);

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::PUB)
            ->will($this->returnValue($this->directoryMock));

        $this->responseMock = $this->createMock(Response::class);
    }

    protected function tearDown()
    {
        unset($this->mediaModel);
    }

    public function testProcessRequestCreatesConfigFileMediaDirectoryIsNotProvided()
    {
        $this->mediaModel = $this->getMediaModel();

        $filePath = '/absolute/path/to/test/file.png';
        $this->directoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValueMap(
                [
                    [null, self::MEDIA_DIRECTORY],
                    [self::RELATIVE_FILE_PATH, $filePath],
                ]
            ));
        $this->configMock->expects($this->once())->method('save');
        $this->sync->expects($this->once())->method('synchronize')->with(self::RELATIVE_FILE_PATH);
        $this->directoryMock->expects($this->once())
            ->method('isReadable')
            ->with(self::RELATIVE_FILE_PATH)
            ->will($this->returnValue(true));
        $this->responseMock->expects($this->once())->method('setFilePath')->with($filePath);
        $this->mediaModel->launch();
    }

    public function testProcessRequestReturnsFileIfItsProperlySynchronized()
    {
        $this->mediaModel = $this->getMediaModel();

        $filePath = '/absolute/path/to/test/file.png';
        $this->sync->expects($this->once())->method('synchronize')->with(self::RELATIVE_FILE_PATH);
        $this->directoryMock->expects($this->once())
            ->method('isReadable')
            ->with(self::RELATIVE_FILE_PATH)
            ->will($this->returnValue(true));
        $this->directoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValueMap(
                [
                    [null, self::MEDIA_DIRECTORY],
                    [self::RELATIVE_FILE_PATH, $filePath],
                ]
            ));
        $this->responseMock->expects($this->once())->method('setFilePath')->with($filePath);
        $this->assertSame($this->responseMock, $this->mediaModel->launch());
    }

    public function testProcessRequestReturnsNotFoundIfFileIsNotSynchronized()
    {
        $this->mediaModel = $this->getMediaModel();

        $this->sync->expects($this->once())->method('synchronize')->with(self::RELATIVE_FILE_PATH);
        $this->directoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with()
            ->will($this->returnValue(self::MEDIA_DIRECTORY));
        $this->directoryMock->expects($this->once())
            ->method('isReadable')
            ->with(self::RELATIVE_FILE_PATH)
            ->will($this->returnValue(false));
        $this->assertSame($this->responseMock, $this->mediaModel->launch());
    }

    /**
     * @param bool $isDeveloper
     * @param int $setBodyCalls
     *
     * @dataProvider catchExceptionDataProvider
     */
    public function testCatchException($isDeveloper, $setBodyCalls)
    {
        /** @var Bootstrap|MockObject $bootstrap */
        $bootstrap = $this->createMock(Bootstrap::class);

        /** @var Exception|MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(404);
        $bootstrap->expects($this->once())
            ->method('isDeveloperMode')
            ->will($this->returnValue($isDeveloper));
        $this->responseMock->expects($this->exactly($setBodyCalls))
            ->method('setBody');
        $this->responseMock->expects($this->once())
            ->method('sendResponse');

        $this->mediaModel = $this->getMediaModel();

        $this->mediaModel->catchException($bootstrap, $exception);
    }

    public function testExceptionWhenIsAllowedReturnsFalse()
    {
        $this->mediaModel = $this->getMediaModel(false);

        $filePath = '/absolute/path/to/test/file.png';
        $this->directoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValueMap(
                [
                    [null, self::MEDIA_DIRECTORY],
                    [self::RELATIVE_FILE_PATH, $filePath],
                ]
            ));
        $this->configMock->expects($this->once())->method('save');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path is not allowed: ' . self::RELATIVE_FILE_PATH);

        $this->mediaModel->launch();
    }

    /**
     * @return array
     */
    public function catchExceptionDataProvider()
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
    protected function getMediaModel(bool $isAllowed = true): Media
    {
        $objectManager = new ObjectManager($this);

        $isAllowedCallback = function () use ($isAllowed) {
            return $isAllowed;
        };

        /** @var Media $mediaClass */
        $mediaClass = $objectManager->getObject(
            Media::class,
            [
                'configFactory' => $this->configFactoryMock,
                'syncFactory' => $this->syncFactoryMock,
                'response' => $this->responseMock,
                'isAllowed' => $isAllowedCallback,
                'mediaDirectory' => false,
                'configCacheFile' => self::CACHE_FILE_PATH,
                'relativeFileName' => self::RELATIVE_FILE_PATH,
                'filesystem' => $this->filesystemMock,
                'placeholderFactory' => $this->createConfiguredMock(
                    PlaceholderFactory::class,
                    [
                        'create' => $this->createMock(Placeholder::class)
                    ]
                ),
            ]
        );

        return $mediaClass;
    }
}
