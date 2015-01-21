<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\Http;

class FileFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Filesystem
     */
    protected $_fileSystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Response\Http
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_fileSystemMock = $this->getMock(
            'Magento\Framework\Filesystem',
            ['getDirectoryWrite'],
            [],
            '',
            false
        );
        $this->_dirMock = $this->getMockBuilder(
            '\Magento\Framework\Filesystem\Directory\Write'
        )->disableOriginalConstructor()->getMock();

        $this->_fileSystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->withAnyParameters()->will(
            $this->returnValue($this->_dirMock)
        );

        $this->_fileSystemMock->expects(
            $this->any()
        )->method(
            'isFile'
        )->withAnyParameters()->will(
            $this->returnValue(0)
        );
        $this->_responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            ['setHeader', 'sendHeaders', 'setHttpResponseCode', 'clearBody', 'setBody', '__wakeup'],
            [],
            '',
            false
        );
        $this->_model = $this->objectManager->getObject(
            'Magento\Framework\App\Response\Http\FileFactory',
            [
                'response' => $this->_responseMock,
                'filesystem' => $this->_fileSystemMock,
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateIfContentDoesntHaveRequiredKeys()
    {
        $this->_model->create('fileName', []);
    }

    /**
     * @expectedException \Exception
     * @exceptedExceptionMessage File not found
     */
    public function testCreateIfFileNotExist()
    {
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];

        $this->_responseMock->expects(
            $this->never()
        )->method(
            'setHeader'
        )->will(
            $this->returnSelf()
        );
        $this->_responseMock->expects(
            $this->never()
        )->method(
            'setHttpResponseCode'
        )->will(
            $this->returnSelf()
        );
        $this->_model->create('fileName', $content);
    }

    /**
     * @expectedException \Magento\Framework\App\Response\Http\TestingPhpExitException
     */
    public function testCreateArrayContent()
    {
        if (!defined('UNIT_TESTING')) {
            define('UNIT_TESTING', 1);
        }
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];

        $this->_dirMock->expects($this->once())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->_dirMock->expects($this->once())
            ->method('stat')
            ->will($this->returnValue(['size' => 100]));
        $this->_responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->once())
            ->method('sendHeaders')
            ->will($this->returnSelf());

        $streamMock = $this->getMockBuilder('Magento\Framework\Filesystem\File\WriteInterface')
            ->disableOriginalConstructor()->getMock();
        $this->_dirMock->expects($this->once())
            ->method('openFile')
            ->will($this->returnValue($streamMock));
        $this->_dirMock->expects($this->never())
            ->method('delete')
            ->will($this->returnValue($streamMock));
        $streamMock->expects($this->at(1))
            ->method('eof')
            ->will($this->returnValue(false));
        $streamMock->expects($this->at(2))
            ->method('eof')
            ->will($this->returnValue(true));
        $streamMock->expects($this->once())
            ->method('read');
        $streamMock->expects($this->once())
            ->method('close');
        $this->_model->create('fileName', $content);
    }

    /**
     * @expectedException \Magento\Framework\App\Response\Http\TestingPhpExitException
     */
    public function testCreateArrayContentRm()
    {
        if (!defined('UNIT_TESTING')) {
            define('UNIT_TESTING', 1);
        }
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file, 'rm' => 1];

        $this->_dirMock->expects($this->once())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->_dirMock->expects($this->once())
            ->method('stat')
            ->will($this->returnValue(['size' => 100]));
        $this->_responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->once())
            ->method('sendHeaders')
            ->will($this->returnSelf());

        $streamMock = $this->getMockBuilder('Magento\Framework\Filesystem\File\WriteInterface')
            ->disableOriginalConstructor()->getMock();
        $this->_dirMock->expects($this->once())
            ->method('openFile')
            ->will($this->returnValue($streamMock));
        $this->_dirMock->expects($this->once())
            ->method('delete')
            ->will($this->returnValue($streamMock));
        $streamMock->expects($this->at(1))
            ->method('eof')
            ->will($this->returnValue(false));
        $streamMock->expects($this->at(2))
            ->method('eof')
            ->will($this->returnValue(true));
        $streamMock->expects($this->once())
            ->method('read');
        $streamMock->expects($this->once())
            ->method('close');
        $this->_model->create('fileName', $content);
    }

    public function testCreateStringContent()
    {
        $this->_dirMock->expects($this->never())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->_dirMock->expects($this->never())
            ->method('stat')
            ->will($this->returnValue(['size' => 100]));
        $this->_responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->once())
            ->method('clearBody')
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->once())
            ->method('setBody')
            ->will($this->returnSelf());
        $this->_responseMock->expects($this->never())
            ->method('sendHeaders')
            ->will($this->returnSelf());

        $streamMock = $this->getMockBuilder('Magento\Framework\Filesystem\File\WriteInterface')
            ->disableOriginalConstructor()->getMock();
        $this->_dirMock->expects($this->never())
            ->method('openFile')
            ->will($this->returnValue($streamMock));
        $this->_dirMock->expects($this->never())
            ->method('delete')
            ->will($this->returnValue($streamMock));
        $streamMock->expects($this->never())
            ->method('eof')
            ->will($this->returnValue(false));
        $streamMock->expects($this->never())
            ->method('read');
        $streamMock->expects($this->never())
            ->method('close');
        $this->assertSame($this->_responseMock, $this->_model->create('fileName', 'content'));
    }
}
