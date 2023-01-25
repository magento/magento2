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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->fileSystemMock =
            $this->getMockBuilder(Filesystem::class)
                ->addMethods(['isFile'])
                ->onlyMethods(['getDirectoryWrite'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->dirMock = $this->getMockBuilder(
            Write::class
        )->disableOriginalConstructor()
            ->getMock();

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
            Http::class,
            ['setHeader', 'sendHeaders', 'setHttpResponseCode', 'clearBody', 'setBody', '__wakeup']
        );
    }

    /**
     * @return void
     */
    public function testCreateIfContentDoesntHaveRequiredKeys(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->getModel()->create('fileName', []);
    }

    /**
     * @return void
     */
    public function testCreateIfFileNotExist(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('File not found');
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];

        $this->responseMock->expects(
            $this->never()
        )->method(
            'setHeader'
        )->willReturnSelf();
        $this->responseMock->expects(
            $this->never()
        )->method(
            'setHttpResponseCode'
        )->willReturnSelf();
        $this->getModel()->create('fileName', $content);
    }

    /**
     * @return void
     */
    public function testCreateArrayContent(): void
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
            ->method('setHeader')->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')->willReturnSelf();

        $streamMock = $this->getMockBuilder(FileWriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->willReturn($streamMock);
        $this->dirMock->expects($this->never())
            ->method('delete')
            ->willReturn($streamMock);
        $streamMock
            ->method('eof')
            ->willReturnOnConsecutiveCalls(false, true);
        $streamMock->expects($this->once())
            ->method('read');
        $streamMock->expects($this->once())
            ->method('close');
        $this->getModelMock()->create('fileName', $content);
    }

    /**
     * @return void
     */
    public function testCreateArrayContentRm(): void
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
            ->method('setHeader')->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')->willReturnSelf();

        $streamMock = $this->getMockBuilder(FileWriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dirMock->expects($this->once())
            ->method('openFile')
            ->willReturn($streamMock);
        $this->dirMock->expects($this->once())
            ->method('delete')
            ->willReturn($streamMock);
        $streamMock
            ->method('eof')
            ->willReturnOnConsecutiveCalls(false, true);
        $streamMock->expects($this->once())
            ->method('read');
        $streamMock->expects($this->once())
            ->method('close');
        $this->getModelMock()->create('fileName', $content);
    }

    /**
     * @return void
     */
    public function testCreateStringContent(): void
    {
        $this->dirMock->expects($this->never())
            ->method('isFile')
            ->willReturn(true);
        $this->dirMock->expects($this->never())
            ->method('stat')
            ->willReturn(['size' => 100]);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200)->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders')->willReturnSelf();
        $this->dirMock->expects($this->once())
            ->method('writeFile')
            ->with('fileName', 'content', 'w+');
        $streamMock = $this->getMockBuilder(FileWriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
     * Get model.
     *
     * @return FileFactory|object
     */
    private function getModel()
    {
        return $this->objectManager->getObject(
            FileFactory::class,
            [
                'response' => $this->responseMock,
                'filesystem' => $this->fileSystemMock
            ]
        );
    }

    /**
     * Get model mock.
     *
     * @return FileFactory|MockObject
     */
    private function getModelMock(): MockObject
    {
        $modelMock = $this->getMockBuilder(FileFactory::class)
            ->onlyMethods([])
            ->setConstructorArgs(
                [
                    'response' => $this->responseMock,
                    'filesystem' => $this->fileSystemMock
                ]
            )
            ->getMock();
        return $modelMock;
    }
}
