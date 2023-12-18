<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Response\Http;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
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
     * @var \Magento\Framework\App\Response\FileFactory|MockObject
     */
    private $fileResponseFactory;

    /**
     * @var FileFactory
     */
    private $model;

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
        $this->fileResponseFactory = $this->createMock(\Magento\Framework\App\Response\FileFactory::class);
        $this->model = new FileFactory($this->responseMock, $this->fileSystemMock, $this->fileResponseFactory);
    }

    /**
     * @return void
     */
    public function testCreateIfContentDoesntHaveRequiredKeys(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->model->create('fileName', []);
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
        $this->model->create('fileName', $content);
    }

    /**
     * @return void
     */
    public function testCreateArrayContent(): void
    {
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file];
        $fileSize = 100;

        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->fileResponseFactory->expects($this->once())
            ->method('create')
            ->with([
                'options' => [
                    'filePath' => $file,
                    'fileName' => 'fileName',
                    'contentType' => 'application/octet-stream',
                    'contentLength' => $fileSize,
                    'directoryCode' => DirectoryList::ROOT,
                    'remove' => false
                ]
            ])
            ->willReturn($responseMock);
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('stat')
            ->willReturn(['size' => $fileSize]);
        $this->model->create('fileName', $content);
    }

    /**
     * @return void
     */
    public function testCreateArrayContentRm(): void
    {
        $file = 'some_file';
        $content = ['type' => 'filename', 'value' => $file, 'rm' => 1];
        $fileSize = 100;

        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('stat')
            ->willReturn(['size' => $fileSize]);
        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->fileResponseFactory->expects($this->once())
            ->method('create')
            ->with([
                'options' => [
                    'filePath' => $file,
                    'fileName' => 'fileName',
                    'contentType' => 'application/octet-stream',
                    'contentLength' => $fileSize,
                    'directoryCode' => DirectoryList::ROOT,
                    'remove' => true
                ]
            ])
            ->willReturn($responseMock);
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->willReturn(true);
        $this->dirMock->expects($this->once())
            ->method('stat')
            ->willReturn(['size' => $fileSize]);
        $this->model->create('fileName', $content);
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
        $this->dirMock->expects($this->once())
            ->method('writeFile')
            ->with('fileName', 'content', 'w+');
        $this->model->create('fileName', 'content');
    }
}
