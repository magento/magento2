<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Download
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpFileFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $writeDirectoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $driverMock;

    protected function setUp()
    {
        $this->writeDirectoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Write')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($this->writeDirectoryMock));

        $this->driverMock = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $this->storageMock = $this->getMockBuilder('Magento\MediaStorage\Helper\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storageFactoryMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\DatabaseFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->httpFileFactoryMock = $this->getMockBuilder('Magento\Framework\App\Response\Http\FileFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->model = new \Magento\Sales\Model\Download(
            $this->filesystemMock,
            $this->storageMock,
            $this->storageFactoryMock,
            $this->httpFileFactoryMock
        );
    }

    public function testInstanceOf()
    {
        $model = new \Magento\Sales\Model\Download(
            $this->filesystemMock,
            $this->storageMock,
            $this->storageFactoryMock,
            $this->httpFileFactoryMock
        );
        $this->assertInstanceOf('Magento\Sales\Model\Download', $model);
    }

    /**
     * @param $realPatchCheck
     * @param $isFile
     * @param $isReadable
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider dataProviderForTestDownloadFileException
     */
    public function testDownloadFileException($realPatchCheck, $isFile, $isReadable)
    {
        $info = ['order_path' => 'test/path', 'quote_path' => 'test/path2', 'title' => 'test title'];

        $this->writeDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));
        $this->writeDirectoryMock->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->driverMock);
        $this->driverMock->expects($this->any())->method('getRealPath')->willReturn($realPatchCheck);
        $this->writeDirectoryMock->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue($isFile));
        $this->writeDirectoryMock->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue($isReadable));

        $this->storageFactoryMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(false));
        $this->httpFileFactoryMock->expects($this->never())->method('create');

        $this->model->downloadFile($info);
    }

    /**
     * @return array
     */
    public function dataProviderForTestDownloadFileException()
    {
        return [
            [1, true, false],
            [1, false, true],
            [false, true, true],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testDownloadFileNoStorage()
    {
        $info = ['order_path' => 'test/path', 'quote_path' => 'test/path2', 'title' => 'test title'];
        $isFile = true;
        $isReadable = false;

        $this->writeDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));
        $this->writeDirectoryMock->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->driverMock);
        $this->driverMock->expects($this->any())->method('getRealPath')->willReturn(true);

        $this->writeDirectoryMock->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue($isFile));
        $this->writeDirectoryMock->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue($isReadable));

        $this->storageMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(true));

        $storageDatabaseMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $storageDatabaseMock->expects($this->at(0))
            ->method('loadByFilename')
            ->with($this->equalTo($info['order_path']))
            ->will($this->returnSelf());
        $storageDatabaseMock->expects($this->at(2))
            ->method('loadByFilename')
            ->with($this->equalTo($info['quote_path']))
            ->will($this->returnSelf());

        $storageDatabaseMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(false));

        $this->storageFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($storageDatabaseMock));
        $this->httpFileFactoryMock->expects($this->never())->method('create');

        $this->model->downloadFile($info);
    }

    public function testDownloadFile()
    {
        $info = ['order_path' => 'test/path', 'quote_path' => 'test/path2', 'title' => 'test title'];
        $isFile = true;
        $isReadable = false;

        $writeMock = $this->getMockBuilder('Magento\Framework\Filesystem\File\Write')
            ->disableOriginalConstructor()
            ->getMock();
        $writeMock->expects($this->any())
            ->method('lock');
        $writeMock->expects($this->any())
            ->method('write');
        $writeMock->expects($this->any())
            ->method('unlock');
        $writeMock->expects($this->any())
            ->method('close');

        $this->writeDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));
        $this->writeDirectoryMock->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->driverMock);
        $this->driverMock->expects($this->any())->method('getRealPath')->willReturn(true);

        $this->writeDirectoryMock->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue($isFile));
        $this->writeDirectoryMock->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue($isReadable));
        $this->writeDirectoryMock->expects($this->any())
            ->method('openFile')
            ->will($this->returnValue($writeMock));
        $this->writeDirectoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($info['order_path'])
            ->will($this->returnArgument(0));

        $this->storageMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(true));

        $storageDatabaseMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Storage\Database')
            ->disableOriginalConstructor()
            ->setMethods(['loadByFilename', 'getId', '__wakeup'])
            ->getMock();
        $storageDatabaseMock->expects($this->any())
            ->method('loadByFilename')
            ->will($this->returnSelf());

        $storageDatabaseMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(true));

        $this->storageFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($storageDatabaseMock));

        $this->httpFileFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $info['title'],
                ['value' => $info['order_path'], 'type' => 'filename'],
                DirectoryList::MEDIA,
                'application/octet-stream',
                null
            );

        $result = $this->model->downloadFile($info);
        $this->assertNull($result);
    }
}
