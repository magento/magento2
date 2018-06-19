<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewfileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawMock;

    /**
     * @var \Magento\Framework\Url\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlDecoderMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystemMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->fileSystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->storage = $this->createMock(\Magento\MediaStorage\Helper\File\Storage::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->urlDecoderMock = $this->createMock(\Magento\Framework\Url\DecoderInterface::class);
        $this->resultRawMock = $this->createMock(\Magento\Framework\Controller\Result\Raw::class);

        $this->resultRawFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RawFactory::class,
            ['create']
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NotFoundException
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteNoParamsShouldThrowException()
    {
        /** @var \Magento\Customer\Controller\Adminhtml\Index\Viewfile $controller */
        $controller = $this->objectManager->getObject(\Magento\Customer\Controller\Adminhtml\Index\Viewfile::class);
        $controller->execute();
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Page not found.
     */
    public function testExecuteInvalidFile()
    {
        $file = '../../../app/etc/env.php';
        $encodedFile = base64_encode($file);
        $fileName = 'customer/' . $file;
        $path = 'path';

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->with('file')->willReturn($encodedFile);

        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($fileName)->willReturn($path);

        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(false);

        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap(
                [
                    [\Magento\Framework\Filesystem::class, $this->fileSystemMock],
                    [\Magento\MediaStorage\Helper\File\Storage::class, $this->storage],
                ]
            );

        $this->urlDecoderMock->expects($this->once())->method('decode')->with($encodedFile)->willReturn($file);
        $fileFactoryMock = $this->createMock(\Magento\Framework\App\Response\Http\FileFactory::class);

        $controller = $this->objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\Viewfile::class,
            [
                'context' => $this->contextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'fileFactory' => $fileFactoryMock,
            ]
        );
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
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(true);

        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap(
                [
                    [\Magento\Framework\Filesystem::class, $this->fileSystemMock],
                    [\Magento\MediaStorage\Helper\File\Storage::class, $this->storage],
                ]
            );

        $this->urlDecoderMock->expects($this->once())->method('decode')->with($decodedFile)->willReturn($file);

        $fileResponse = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $fileFactoryMock = $this->createMock(\Magento\Framework\App\Response\Http\FileFactory::class);
        $fileFactoryMock->expects($this->once())->method('create')->with(
            $path,
            ['type' => 'filename', 'value' => $fileName],
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->willReturn($fileResponse);

        /** @var \Magento\Customer\Controller\Adminhtml\Index\Viewfile $controller */
        $controller = $this->objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\Viewfile::class,
            [
                'context' => $this->contextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'fileFactory' => $fileFactoryMock,
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
            ->willReturnMap([['file', null, null], ['image', null, $decodedFile]]);

        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($fileName)->willReturn($path);
        $this->directoryMock->expects($this->once())->method('stat')->with($fileName)->willReturn($stat);

        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(true);

        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap(
                [
                    [\Magento\Framework\Filesystem::class, $this->fileSystemMock],
                    [\Magento\MediaStorage\Helper\File\Storage::class, $this->storage],
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
            \Magento\Framework\Controller\Result\RawFactory::class,
            ['create']
        );
        $this->resultRawFactoryMock->expects($this->once())->method('create')->willReturn($this->resultRawMock);

        /** @var \Magento\Customer\Controller\Adminhtml\Index\Viewfile $controller */
        $controller = $this->objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\Viewfile::class,
            [
                'context' => $this->contextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
            ]
        );
        $this->assertSame($this->resultRawMock, $controller->execute());
    }
}
