<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model;

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

    protected function setUp()
    {
        $this->writeDirectoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Write')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\App\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::ROOT_DIR)
            ->will($this->returnValue($this->writeDirectoryMock));

        $this->storageMock = $this->getMockBuilder('Magento\Core\Helper\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storageFactoryMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\DatabaseFactory')
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
     * @expectedException \Exception
     */
    public function testDownloadFileException()
    {
        $info = ['order_path' => 'test/path', 'quote_path' => 'test/path2', 'title' => 'test title'];
        $isFile = true;
        $isReadable = false;

        $this->writeDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));
        $this->writeDirectoryMock->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue($isFile));
        $this->writeDirectoryMock->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue($isReadable));

        $this->storageFactoryMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(false));

        $this->model->downloadFile($info);
    }

    /**
     * @expectedException \Exception
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
            ->method('isFile')
            ->will($this->returnValue($isFile));
        $this->writeDirectoryMock->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue($isReadable));

        $this->storageMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(true));
        $this->storageMock->expects($this->any())
            ->method('getMediaRelativePath')
            ->will($this->returnArgument(0));

        $storageDatabaseMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
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
        $this->storageMock->expects($this->any())
            ->method('getMediaRelativePath')
            ->will($this->returnArgument(0));

        $storageDatabaseMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
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
                \Magento\Framework\App\Filesystem::ROOT_DIR,
                'application/octet-stream',
                null
            );

        $result = $this->model->downloadFile($info);
        $this->assertNull($result);
    }
}
