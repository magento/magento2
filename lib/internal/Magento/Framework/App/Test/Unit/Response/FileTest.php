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
        $directory = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($directory);
        $this->expectExceptionMessage('File path is required.');
        $this->getModel($options)->sendResponse();
    }

    public function testSendResponseWithFileThatDoesNotExist(): void
    {
        $options = [
            'filePath' => 'path/to/file'
        ];
        $directory = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($directory);
        $directory->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $this->expectExceptionMessage("File 'path/to/file' does not exists.");
        $this->getModel($options)->sendResponse();
    }

    public function testSendResponseWithFilePathOnly(): void
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
        $directory = $this->getMockForAbstractClass(WriteInterface::class);
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
        $directory->expects($this->never())
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
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($directory);
        $this->mimeMock->expects($this->once())
            ->method('getMimeType')
            ->willReturn($fileMimetype);
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->withConsecutive(
                ['Content-type', $fileMimetype, true],
                ['Content-Length', $fileSize, true],
                ['Content-Disposition', 'attachment; filename="' . $fileName . '"', true],
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
        $directory = $this->getMockForAbstractClass(WriteInterface::class);
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
        $directory->expects($this->once())
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
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($directory);
        $this->mimeMock->expects($this->once())
            ->method('getMimeType')
            ->willReturn($fileMimetype);
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200);
        $this->responseMock->expects($this->exactly(6))
            ->method('setHeader')
            ->withConsecutive(
                ['Content-type', $fileMimetype, true],
                ['Content-Length', $fileSize, true],
                ['Content-Disposition', 'attachment; filename="' . $fileName . '"', true],
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

    public function testSetHeader(): void
    {
        $model = $this->getModel();
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-type', 1024, true)
            ->willReturnSelf();
        $this->assertSame($model, $model->setHeader('Content-type', 1024, true));
    }

    public function testGetHeader(): void
    {
        $model = $this->getModel();
        $this->responseMock->expects($this->once())
            ->method('getHeader')
            ->with('Content-type')
            ->willReturn(2048);
        $this->assertEquals(2048, $model->getHeader('Content-type'));
    }

    public function testClearHeader(): void
    {
        $model = $this->getModel();
        $this->responseMock->expects($this->once())
            ->method('clearHeader')
            ->with('Content-type')
            ->willReturnSelf();
        $this->assertSame($model, $model->clearHeader('Content-type'));
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
