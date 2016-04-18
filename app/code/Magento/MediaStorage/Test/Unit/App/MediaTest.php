<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\App;

use Magento\Framework\App\Filesystem\DirectoryList;

class MediaTest extends \PHPUnit_Framework_TestCase
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
        $this->configMock = $this->getMock('Magento\MediaStorage\Model\File\Storage\Config', [], [], '', false);
        $this->sync = $this->getMock('Magento\MediaStorage\Model\File\Storage\Synchronization', [], [], '', false);
        $this->configFactoryMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\ConfigFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->configFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->configMock));
        $this->syncFactoryMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\SynchronizationFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->syncFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->sync));

        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->directoryMock = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($this->directoryMock));

        $this->responseMock = $this->getMock('Magento\MediaStorage\Model\File\Storage\Response', [], [], '', false);

        $this->model = new \Magento\MediaStorage\App\Media(
            $this->configFactoryMock,
            $this->syncFactoryMock,
            $this->responseMock,
            $this->closure,
            self::MEDIA_DIRECTORY,
            self::CACHE_FILE_PATH,
            self::RELATIVE_FILE_PATH,
            $this->filesystemMock
        );
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testProcessRequestCreatesConfigFileMediaDirectoryIsNotProvided()
    {
        $this->model = new \Magento\MediaStorage\App\Media(
            $this->configFactoryMock,
            $this->syncFactoryMock,
            $this->responseMock,
            $this->closure,
            false,
            self::CACHE_FILE_PATH,
            self::RELATIVE_FILE_PATH,
            $this->filesystemMock
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

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The specified path is not allowed.
     */
    public function testProcessRequestReturnsNotFoundResponseIfResourceIsNotAllowed()
    {
        $this->closure = function () {
            return false;
        };
        $this->model = new \Magento\MediaStorage\App\Media(
            $this->configFactoryMock,
            $this->syncFactoryMock,
            $this->responseMock,
            $this->closure,
            false,
            self::CACHE_FILE_PATH,
            self::RELATIVE_FILE_PATH,
            $this->filesystemMock
        );
        $this->directoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with()
            ->will($this->returnValue(self::MEDIA_DIRECTORY));
        $this->configMock->expects($this->once())->method('getAllowedResources')->will($this->returnValue(false));
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
        $this->responseMock->expects($this->once())->method('setHttpResponseCode')->with(404);
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
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        $exception = $this->getMock('Exception', [], [], '', false);
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

    public function catchExceptionDataProvider()
    {
        return [
            'default mode' => [false, 0],
            'developer mode' => [true, 1],
        ];
    }
}
