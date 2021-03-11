<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Response\Http;

class FileFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Filesystem
     */
    protected $fileSystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\App\Response\Http
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dirMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fileSystemMock =
            $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite', 'isFile']);
        $this->dirMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\Write::class
        )->disableOriginalConstructor()->getMock();

        $this->fileSystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->withAnyParameters()->willReturn(
            $this->dirMock
        );

        $this->fileSystemMock->expects(
            $this->any()
        )->method(
            'isFile'
        )->withAnyParameters()->willReturn(
            0
        );
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['setHeader', 'sendHeaders', 'setHttpResponseCode', 'clearBody', 'setBody', '__wakeup']
        );
    }

    /**
     */
    public function testCreateIfContentDoesntHaveRequiredKeys()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getModel()->create('fileName', []);
    }

    /**
     */
    public function testCreateIfFileNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File not found');

        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];

        $this->responseMock->expects(
            $this->never()
        )->method(
            'setHeader'
        )->willReturnSelf(
            
        );
        $this->responseMock->expects(
            $this->never()
        )->method(
            'setHttpResponseCode'
        )->willReturnSelf(
            
        );
        $this->getModel()->create('fileName', $content);
    }

    public function testCreateArrayContent()
    {
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];

        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('stat')
            ->willReturn(['size' => 100]);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')
            ->willReturnSelf();

        $streamMock = $this->getMockBuilder(\Magento\Framework\Filesystem\File\WriteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->willReturn($streamMock);
        $this->dirMock->expects($this->never())
            ->method('delete')
            ->willReturn($streamMock);
        $streamMock->expects($this->at(1))
            ->method('eof')
            ->willReturn(false);
        $streamMock->expects($this->at(2))
            ->method('eof')
            ->willReturn(true);
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
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('stat')
            ->willReturn(['size' => 100]);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')
            ->willReturnSelf();

        $streamMock = $this->getMockBuilder(\Magento\Framework\Filesystem\File\WriteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->willReturn($streamMock);
        $this->dirMock->expects($this->once())
            ->method('delete')
            ->willReturn($streamMock);
        $streamMock->expects($this->at(1))
            ->method('eof')
            ->willReturn(false);
        $streamMock->expects($this->at(2))
            ->method('eof')
            ->willReturn(true);
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
            ->willReturn(true);
        $this->dirMock->expects($this->never())
            ->method('stat')
            ->willReturn(['size' => 100]);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')
            ->willReturnSelf();
        $this->dirMock->expects($this->once())
            ->method('writeFile')
            ->with('fileName', 'content', 'w+');
        $streamMock = $this->getMockBuilder(\Magento\Framework\Filesystem\File\WriteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->willReturn($streamMock);
        $streamMock->expects($this->once())
            ->method('eof')
            ->willReturn(true);
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
            \Magento\Framework\App\Response\Http\FileFactory::class,
            [
                'response' => $this->responseMock,
                'filesystem' => $this->fileSystemMock,
            ]
        );
    }

    /**
     * Get model mock
     *
     * @return \Magento\Framework\App\Response\Http\FileFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    private function getModelMock()
    {
        $modelMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
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
