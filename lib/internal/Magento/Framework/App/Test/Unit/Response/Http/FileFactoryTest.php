<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Response\Http;

use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileFactoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject|Filesystem
     */
    protected $fileSystemMock;

    /**
     * @var MockObject|Http
     */
    protected $responseMock;

    /**
     * @var DirectoryWriteInterface|MockObject
     */
    protected $dirMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->fileSystemMock =
            $this->createPartialMock(Filesystem::class, ['getDirectoryWrite', 'isFile']);
        $this->dirMock = $this->getMockBuilder(
            Write::class
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
        $this->responseMock = $this->createPartialMock(
            Http::class,
            ['setHeader', 'sendHeaders', 'setHttpResponseCode', 'clearBody', 'setBody', '__wakeup']
        );
    }

    public function testCreateIfContentDoesntHaveRequiredKeys()
    {
        $this->expectException('InvalidArgumentException');
        $this->getModel()->create('fileName', []);
    }

    public function testCreateIfFileNotExist()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('File not found');
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

        $streamMock = $this->getMockBuilder(FileWriteInterface::class)
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

        $streamMock = $this->getMockBuilder(FileWriteInterface::class)
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
        $streamMock = $this->getMockBuilder(FileWriteInterface::class)
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
     * @return FileFactory
     */
    private function getModel()
    {
        return $this->objectManager->getObject(
            FileFactory::class,
            [
                'response' => $this->responseMock,
                'filesystem' => $this->fileSystemMock,
            ]
        );
    }

    /**
     * Get model mock
     *
     * @return FileFactory|MockObject
     */
    private function getModelMock()
    {
        $modelMock = $this->getMockBuilder(FileFactory::class)
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    'response' => $this->responseMock,
                    'filesystem' => $this->fileSystemMock,
                ]
            )
            ->getMock();
        return $modelMock;
    }
}
