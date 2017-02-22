<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Response\Http;

class FileFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Filesystem
     */
    protected $fileSystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Response\Http
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dirMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fileSystemMock = $this->getMock(
            'Magento\Framework\Filesystem',
            ['getDirectoryWrite'],
            [],
            '',
            false
        );
        $this->dirMock = $this->getMockBuilder(
            '\Magento\Framework\Filesystem\Directory\Write'
        )->disableOriginalConstructor()->getMock();

        $this->fileSystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->withAnyParameters()->will(
            $this->returnValue($this->dirMock)
        );

        $this->fileSystemMock->expects(
            $this->any()
        )->method(
            'isFile'
        )->withAnyParameters()->will(
            $this->returnValue(0)
        );
        $this->responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            ['setHeader', 'sendHeaders', 'setHttpResponseCode', 'clearBody', 'setBody', '__wakeup'],
            [],
            '',
            false
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateIfContentDoesntHaveRequiredKeys()
    {
        $this->getModel()->create('fileName', []);
    }

    /**
     * @expectedException \Exception
     * @exceptedExceptionMessage File not found
     */
    public function testCreateIfFileNotExist()
    {
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];

        $this->responseMock->expects(
            $this->never()
        )->method(
            'setHeader'
        )->will(
            $this->returnSelf()
        );
        $this->responseMock->expects(
            $this->never()
        )->method(
            'setHttpResponseCode'
        )->will(
            $this->returnSelf()
        );
        $this->getModel()->create('fileName', $content);
    }

    public function testCreateArrayContent()
    {
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];

        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('stat')
            ->will($this->returnValue(['size' => 100]));
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->will($this->returnSelf());
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->will($this->returnSelf());
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')
            ->will($this->returnSelf());

        $streamMock = $this->getMockBuilder('Magento\Framework\Filesystem\File\WriteInterface')
            ->disableOriginalConstructor()->getMock();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->will($this->returnValue($streamMock));
        $this->dirMock->expects($this->never())
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
        $this->getModelMock()->create('fileName', $content);
    }

    public function testCreateArrayContentRm()
    {
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file, 'rm' => 1];

        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('stat')
            ->will($this->returnValue(['size' => 100]));
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->will($this->returnSelf());
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->will($this->returnSelf());
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')
            ->will($this->returnSelf());

        $streamMock = $this->getMockBuilder('Magento\Framework\Filesystem\File\WriteInterface')
            ->disableOriginalConstructor()->getMock();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->will($this->returnValue($streamMock));
        $this->dirMock->expects($this->once())
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
        $this->getModelMock()->create('fileName', $content);
    }

    public function testCreateStringContent()
    {
        $this->dirMock->expects($this->never())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->never())
            ->method('stat')
            ->will($this->returnValue(['size' => 100]));
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->will($this->returnSelf());
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->will($this->returnSelf());
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')
            ->will($this->returnSelf());
        $this->dirMock->expects($this->once())
            ->method('writeFile')
            ->with('fileName', 'content', 'w+');
        $streamMock = $this->getMockBuilder('Magento\Framework\Filesystem\File\WriteInterface')
            ->disableOriginalConstructor()->getMock();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->will($this->returnValue($streamMock));
        $streamMock->expects($this->once())
            ->method('eof')
            ->will($this->returnValue(true));
        $streamMock->expects($this->once())
            ->method('close');
        $this->getModelMock()->create('fileName', 'content');
    }

    /**
     * Get model
     *
     * @return \Magento\Framework\App\Response\Http\FileFactory
     */
    private function getModel()
    {
        return $this->objectManager->getObject(
            'Magento\Framework\App\Response\Http\FileFactory',
            [
                'response' => $this->responseMock,
                'filesystem' => $this->fileSystemMock,
            ]
        );
    }

    /**
     * Get model mock
     *
     * @return \Magento\Framework\App\Response\Http\FileFactory | \PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getModelMock()
    {
        $modelMock = $this->getMock(
            'Magento\Framework\App\Response\Http\FileFactory',
            ['callExit'],
            [
                'response' => $this->responseMock,
                'filesystem' => $this->fileSystemMock,
            ]
        );
        return $modelMock;
    }
}
