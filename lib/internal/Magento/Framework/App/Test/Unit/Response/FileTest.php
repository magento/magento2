<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Response;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\File;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File\Mime;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends TestCase
{
    /**
     * @var RequestHttp|MockObject
     */
    private $requestMock;
    /**
     * @var CookieMetadataFactory|MockObject
     */
    private $cookieMetadataFactoryMock;
    /**
     * @var CookieManagerInterface|MockObject
     */
    private $cookieManagerMock;
    /**
     * @var Context|MockObject
     */
    private $contextMock;
    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;
    /**
     * @var ConfigInterface|MockObject
     */
    private $sessionConfigMock;
    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;
    /**
     * @var Mime|MockObject
     */
    private $mimeMock;
    /**
     * @var Http|MockObject
     */
    private $responseMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->requestMock = $this->getMockBuilder(RequestHttp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mimeMock = $this->getMockBuilder(Mime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSendResponseWithMissingFilePath(): void
    {
        $options = [];
        $this->expectExceptionMessage('File name is required.');
        $this->getModel($options)->sendResponse();
    }

    public function testSendResponseWithFileThatDoesNotExist(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $directory = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($directory);
        $directory->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $this->expectExceptionMessage("File 'path/to/file.pdf' does not exists.");
        $this->getModel($options)->sendResponse();
    }

    public function testSendResponseWithFilePath(): void
    {
        $fileSize = 1024;
        $filePath = 'path/to/file.pdf';
        $fileAbsolutePath = 'path/to/root/path/to/file.pdf';
        $fileName = 'file.pdf';
        $fileMimetype = 'application/pdf';
        $stat = [
            'size' => $fileSize
        ];
        $options = [
            'filePath' => $filePath
        ];
        $directory = $this->getMockForAbstractClass(ReadInterface::class);
        $directory->expects($this->once())
            ->method('isExist')
            ->with($filePath)
            ->willReturn(true);
        $directory->expects($this->once())
            ->method('getAbsolutePath')
            ->with($filePath)
            ->willReturn($fileAbsolutePath);
        $directory->expects($this->exactly(2))
            ->method('stat')
            ->with($filePath)
            ->willReturn($stat);
        $writeDirectory = $this->getMockForAbstractClass(WriteInterface::class);
        $writeDirectory->expects($this->never())
            ->method('delete')
            ->with($filePath);
        $stream = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\File\WriteInterface::class);
        $directory->expects($this->once())
            ->method('openFile')
            ->with($filePath)
            ->willReturn($stream);
        $stream->expects($this->once())
            ->method('eof')
            ->willReturn(true);
        $stream->expects($this->once())
            ->method('close');
        $this->filesystemMock->expects($this->exactly(2))
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($directory);
        $this->filesystemMock->expects($this->never())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($writeDirectory);
        $this->mimeMock->expects($this->once())
            ->method('getMimeType')
            ->willReturn($fileMimetype);
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->withConsecutive(
                ['Content-Disposition', 'attachment; filename="' . $fileName . '"', true],
                ['Content-Type', $fileMimetype, true],
                ['Content-Length', $fileSize, true],
                ['Pragma', 'public', true],
                ['Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true],
                [
                    'Last-Modified',
                    $this->callback(fn (string $str) => preg_match('/\+|\-\d{4}$/', $str) !== false),
                    true
                ],
            )
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders');
        $this->getModel($options)->sendResponse();
    }

    public function testSendResponseWithRemoveOption(): void
    {
        $fileSize = 1024;
        $filePath = 'path/to/file.pdf';
        $fileAbsolutePath = 'path/to/root/path/to/file.pdf';
        $fileName = 'file.pdf';
        $fileMimetype = 'application/pdf';
        $stat = [
            'size' => $fileSize
        ];
        $options = [
            'filePath' => $filePath,
            'remove' => true
        ];
        $directory = $this->getMockForAbstractClass(ReadInterface::class);
        $directory->expects($this->once())
            ->method('isExist')
            ->with($filePath)
            ->willReturn(true);
        $directory->expects($this->once())
            ->method('getAbsolutePath')
            ->with($filePath)
            ->willReturn($fileAbsolutePath);
        $directory->expects($this->exactly(2))
            ->method('stat')
            ->with($filePath)
            ->willReturn($stat);
        $writeDirectory = $this->getMockForAbstractClass(WriteInterface::class);
        $writeDirectory->expects($this->once())
            ->method('delete')
            ->with($filePath);
        $stream = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\File\WriteInterface::class);
        $directory->expects($this->once())
            ->method('openFile')
            ->with($filePath)
            ->willReturn($stream);
        $stream->expects($this->once())
            ->method('eof')
            ->willReturn(true);
        $stream->expects($this->once())
            ->method('close');
        $this->filesystemMock->expects($this->exactly(2))
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($directory);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($writeDirectory);
        $this->mimeMock->expects($this->once())
            ->method('getMimeType')
            ->willReturn($fileMimetype);
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->withConsecutive(
                ['Content-Disposition', 'attachment; filename="' . $fileName . '"', true],
                ['Content-Type', $fileMimetype, true],
                ['Content-Length', $fileSize, true],
                ['Pragma', 'public', true],
                ['Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true],
                [
                    'Last-Modified',
                    $this->callback(fn (string $str) => preg_match('/\+|\-\d{4}$/', $str) !== false),
                    true
                ],
            )
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('sendHeaders');
        $this->getModel($options)->sendResponse();
    }

    public function testSendResponseWithRawContent(): void
    {
        $fileMimetype = 'application/octet-stream';
        $fileSize = 18;
        $fileName = 'file.pdf';
        $options = [
            'fileName' => $fileName,
        ];
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->withConsecutive(
                ['Content-Disposition', 'attachment; filename="' . $fileName . '"', false],
                ['Content-Type', $fileMimetype, false],
                ['Content-Length', $fileSize, false],
                ['Pragma', 'public', false],
                ['Cache-Control', 'must-revalidate, post-check=0, pre-check=0', false],
                [
                    'Last-Modified',
                    $this->callback(fn (string $str) => preg_match('/\+|\-\d{4}$/', $str) !== false),
                    false
                ],
            )
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('getContent')
            ->willReturn('Bienvenue à Paris');
        $this->getModel($options)->sendResponse();
    }

    public function testSetHeader(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $model = $this->getModel($options);
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', 1024, true)
            ->willReturnSelf();
        $this->assertSame($model, $model->setHeader('Content-Type', 1024, true));
    }

    public function testGetHeader(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $model = $this->getModel($options);
        $this->responseMock->expects($this->once())
            ->method('getHeader')
            ->with('Content-Type')
            ->willReturn(2048);
        $this->assertEquals(2048, $model->getHeader('Content-Type'));
    }

    public function testClearHeader(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $model = $this->getModel($options);
        $this->responseMock->expects($this->once())
            ->method('clearHeader')
            ->with('Content-Type')
            ->willReturnSelf();
        $this->assertSame($model, $model->clearHeader('Content-Type'));
    }

    public function testSetBody(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $model = $this->getModel($options);
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with('Hello World')
            ->willReturnSelf();
        $this->assertSame($model, $model->setBody('Hello World'));
    }

    public function testAppendBody(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $model = $this->getModel($options);
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with('Hello World')
            ->willReturnSelf();
        $this->assertSame($model, $model->appendBody('Hello World'));
    }

    public function testGetContent(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $model = $this->getModel($options);
        $this->responseMock->expects($this->once())
            ->method('getContent')
            ->willReturn('Hello World');
        $this->assertEquals('Hello World', $model->getContent());
    }

    public function testSetContent(): void
    {
        $options = [
            'filePath' => 'path/to/file.pdf'
        ];
        $model = $this->getModel($options);
        $this->responseMock->expects($this->once())
            ->method('setContent')
            ->with('Hello World')
            ->willReturnSelf();
        $this->assertSame($model, $model->setContent('Hello World'));
    }

    private function getModel(array $options = []): File
    {
        return new File(
            $this->requestMock,
            $this->cookieManagerMock,
            $this->cookieMetadataFactoryMock,
            $this->contextMock,
            $this->dateTimeMock,
            $this->sessionConfigMock,
            $this->responseMock,
            $this->filesystemMock,
            $this->mimeMock,
            $options
        );
    }
}
