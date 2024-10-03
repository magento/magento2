<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Controller\Adminhtml\Index\Viewfile;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\DecoderInterface;
use Magento\MediaStorage\Helper\File\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewfileTest extends TestCase
{
    /**
     * @var RawFactory|MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var Raw|MockObject
     */
    protected $resultRawMock;

    /**
     * @var DecoderInterface|MockObject
     */
    protected $urlDecoderMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Storage|MockObject
     */
    protected $storage;

    /**
     * @var Filesystem|MockObject
     */
    protected $fileSystemMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var ReadInterface|MockObject
     */
    protected $directoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->storage = $this->createMock(Storage::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->urlDecoderMock = $this->getMockForAbstractClass(DecoderInterface::class);
        $this->resultRawMock = $this->createMock(Raw::class);

        $this->resultRawFactoryMock = $this->createPartialMock(
            RawFactory::class,
            ['create']
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testExecuteNoParamsShouldThrowException()
    {
        $this->expectException(NotFoundException::class);

        /** @var Viewfile $controller */
        $controller = $this->objectManager->getObject(Viewfile::class);
        $controller->execute();
    }

    public function testExecuteParamFile()
    {
        $decodedFile = 'decoded_file';
        $file = 'file';
        $fileName = 'customer/' . $file;
        $path = 'path';

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->with('file')->willReturn($decodedFile);

        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($fileName)->willReturn($path);

        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(true);

        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap(
                [
                    [Filesystem::class, $this->fileSystemMock],
                    [Storage::class, $this->storage]
                ]
            );

        $this->urlDecoderMock->expects($this->once())->method('decode')->with($decodedFile)->willReturn($file);

        $fileResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        $fileFactoryMock = $this->createMock(FileFactory::class);
        $fileFactoryMock->expects($this->once())->method('create')->with(
            $path,
            ['type' => 'filename', 'value' => $fileName],
            DirectoryList::MEDIA
        )->willReturn($fileResponse);

        /** @var Viewfile $controller */
        $controller = $this->objectManager->getObject(
            Viewfile::class,
            [
                'context' => $this->contextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'fileFactory' => $fileFactoryMock
            ]
        );
        $controller->execute();
    }

    public function testExecuteGetParamImage()
    {
        $decodedFile = 'decoded_file';
        $file = 'file';
        $fileName = 'customer/' . $file;
        $path = 'path';
        $stat = ['size' => 10, 'mtime' => 10];

        $this->requestMock->expects($this->any())->method('getParam')
            ->willReturnMap([['file', '', ''], ['image', '', $decodedFile]]);

        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($fileName)->willReturn($path);
        $this->directoryMock->expects($this->once())->method('stat')->with($fileName)->willReturn($stat);

        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(true);

        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap(
                [
                    [Filesystem::class, $this->fileSystemMock],
                    [Storage::class, $this->storage]
                ]
            );

        $this->urlDecoderMock->expects($this->once())->method('decode')->with($decodedFile)->willReturn($file);

        $this->resultRawMock->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->resultRawMock->expects($this->any())->method('setHeader')
            ->willReturnMap(
                [
                    ['Pragma', 'public', true, $this->resultRawMock],
                    ['Content-type', 'application/octet-stream', true, $this->resultRawMock],
                    ['Content-Length', $stat['size'], false, $this->resultRawMock],
                    ['Pragma', 'public', true, $this->resultRawMock],
                ]
            );

        $this->resultRawFactoryMock = $this->createPartialMock(
            RawFactory::class,
            ['create']
        );
        $this->resultRawFactoryMock->expects($this->once())->method('create')->willReturn($this->resultRawMock);

        /** @var Viewfile $controller */
        $controller = $this->objectManager->getObject(
            Viewfile::class,
            [
                'context' => $this->contextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'resultRawFactory' => $this->resultRawFactoryMock
            ]
        );
        $this->assertSame($this->resultRawMock, $controller->execute());
    }

    public function testExecuteInvalidFile()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Page not found.');

        $file = '../../../app/etc/env.php';
        $decodedFile = base64_encode($file);
        $fileName = 'customer/' . $file;
        $path = 'path';

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->with('file')->willReturn($decodedFile);

        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($fileName)->willReturn($path);

        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(false);

        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap(
                [
                    [Filesystem::class, $this->fileSystemMock],
                    [Storage::class, $this->storage],
                ]
            );

        $this->urlDecoderMock->expects($this->once())->method('decode')->with($decodedFile)->willReturn($file);
        $fileFactoryMock = $this->createMock(
            FileFactory::class
        );

        $controller = $this->objectManager->getObject(
            Viewfile::class,
            [
                'context' => $this->contextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'fileFactory' => $fileFactoryMock,
            ]
        );
        $controller->execute();
    }
}
