<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\App;

use Magento\Catalog\Model\View\Asset\Placeholder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class MediaTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MediaTest extends \PHPUnit\Framework\TestCase
{
    const MEDIA_DIRECTORY = 'mediaDirectory';
    const RELATIVE_FILE_PATH = 'test/file.png';
    const CACHE_FILE_PATH = 'var';

    /**
     * @var \Magento\MediaStorage\App\Media
     */
    private $model;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configFactoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\SynchronizationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $syncFactoryMock;

    /**
     * @var callable
     */
    private $closure;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sync;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryMock;

    protected function setUp()
    {
        $this->closure = function () {
            return true;
        };
        $this->configMock = $this->createMock(\Magento\MediaStorage\Model\File\Storage\Config::class);
        $this->sync = $this->createMock(\Magento\MediaStorage\Model\File\Storage\Synchronization::class);
        $this->configFactoryMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\ConfigFactory::class,
            ['create']
        );
        $this->configFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->configMock));
        $this->syncFactoryMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\SynchronizationFactory::class,
            ['create']
        );
        $this->syncFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->sync));

        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->directoryMock = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::PUB)
            ->will($this->returnValue($this->directoryMock));

        $this->responseMock = $this->createMock(\Magento\MediaStorage\Model\File\Storage\Response::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\MediaStorage\App\Media::class,
            [
                'configFactory' => $this->configFactoryMock,
                'syncFactory' => $this->syncFactoryMock,
                'response' => $this->responseMock,
                'isAllowed' => $this->closure,
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
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testProcessRequestCreatesConfigFileMediaDirectoryIsNotProvided()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\MediaStorage\App\Media::class,
            [
                'configFactory' => $this->configFactoryMock,
                'syncFactory' => $this->syncFactoryMock,
                'response' => $this->responseMock,
                'isAllowed' => $this->closure,
                'mediaDirectory' => false,
                'configCacheFile' => self::CACHE_FILE_PATH,
                'relativeFileName' => self::RELATIVE_FILE_PATH,
                'filesystem' => $this->filesystemMock
            ]
        );
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
        $this->model->launch();
    }

    public function testProcessRequestReturnsFileIfItsProperlySynchronized()
    {
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
        $this->assertSame($this->responseMock, $this->model->launch());
    }

    public function testProcessRequestReturnsNotFoundIfFileIsNotSynchronized()
    {
        $this->sync->expects($this->once())->method('synchronize')->with(self::RELATIVE_FILE_PATH);
        $this->directoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with()
            ->will($this->returnValue(self::MEDIA_DIRECTORY));
        $this->directoryMock->expects($this->once())
            ->method('isReadable')
            ->with(self::RELATIVE_FILE_PATH)
            ->will($this->returnValue(false));
        $this->assertSame($this->responseMock, $this->model->launch());
    }

    /**
     * @param bool $isDeveloper
     * @param int $setBodyCalls
     *
     * @dataProvider catchExceptionDataProvider
     */
    public function testCatchException($isDeveloper, $setBodyCalls)
    {
        $bootstrap = $this->createMock(\Magento\Framework\App\Bootstrap::class);
        $exception = $this->createMock(\Exception::class);
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
        $this->model->catchException($bootstrap, $exception);
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
}
