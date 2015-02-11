<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\Index;

class ViewfileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Magento\Framework\App\Action\NotFoundException
     * @expectedException \Magento\Framework\App\Action\NotFoundException
     */
    public function testExecuteNoParamsShouldThrowException()
    {
        /** @var \Magento\Customer\Controller\Adminhtml\Index\Viewfile $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Customer\Controller\Adminhtml\Index\Viewfile');
        $controller->execute();
    }

    public function testExecuteParamFile()
    {
        $decodedFile = 'decoded_file';
        $file = 'file';
        $fileName = 'customer/' . $file;
        $path = 'path';

        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $requestMock->expects($this->atLeastOnce())->method('getParam')->with('file')->willReturn($decodedFile);

        $responseMock = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);

        $directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $directoryMock->expects($this->once())->method('getAbsolutePath')->with($fileName)->willReturn($path);

        $fileSystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $fileSystemMock->expects($this->once())->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->willReturn($directoryMock);

        $storage = $this->getMock('Magento\Core\Helper\File\Storage', [], [], '', false);
        $storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(true);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->at(0))->method('get')->with('Magento\Framework\Filesystem')
            ->willReturn($fileSystemMock);
        $objectManager->expects($this->at(1))->method('get')->with('Magento\Core\Helper\File\Storage')
            ->willReturn($storage);

        $contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $contextMock->expects($this->once())->method('getResponse')->willReturn($responseMock);
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($objectManager);

        $urlDecoderMock = $this->getMock('Magento\Framework\Url\DecoderInterface', [], [], '', false);
        $urlDecoderMock->expects($this->once())->method('decode')->with($decodedFile)->willReturn($file);

        $resultRawMock = $this->getMock('Magento\Framework\Controller\Result\Raw', [], [], '', false);
        $resultRawFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RawFactory',
            ['create'],
            [],
            '',
            false
        );
        $resultRawFactoryMock->expects($this->once())->method('create')->willReturn($resultRawMock);

        $fileResponse = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);
        $fileFactoryMock = $this->getMock('Magento\Framework\App\Response\Http\FileFactory', [], [], '', false);
        $fileFactoryMock->expects($this->once())->method('create')->with(
            $path,
            ['type' => 'filename', 'value' => $fileName],
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->willReturn($fileResponse);

        /** @var \Magento\Customer\Controller\Adminhtml\Index\Viewfile $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\Viewfile',
            [
                'context' => $contextMock,
                'urlDecoder' => $urlDecoderMock,
                'resultRawFactory' => $resultRawFactoryMock,
                'fileFactory' => $fileFactoryMock
            ]
        );
        $this->assertSame($resultRawMock, $controller->execute());
        $this->assertSame($fileResponse, $controller->getResponse());
    }

    public function testExecuteGetParamImage()
    {
        $decodedFile = 'decoded_file';
        $file = 'file';
        $fileName = 'customer/' . $file;
        $path = 'path';
        $stat = ['size' => 10, 'mtime' => 10];

        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $requestMock->expects($this->at(0))->method('getParam')->with('file')->willReturn(null);
        $requestMock->expects($this->at(1))->method('getParam')->with('image')->willReturn($decodedFile);
        $requestMock->expects($this->at(2))->method('getParam')->with('image')->willReturn($decodedFile);

        $responseMock = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);

        $directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $directoryMock->expects($this->once())->method('getAbsolutePath')->with($fileName)->willReturn($path);
        $directoryMock->expects($this->once())->method('stat')->with($path)->willReturn($stat);

        $fileSystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $fileSystemMock->expects($this->once())->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->willReturn($directoryMock);

        $storage = $this->getMock('Magento\Core\Helper\File\Storage', [], [], '', false);
        $storage->expects($this->once())->method('processStorageFile')->with($path)->willReturn(true);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->at(0))->method('get')->with('Magento\Framework\Filesystem')
            ->willReturn($fileSystemMock);
        $objectManager->expects($this->at(1))->method('get')->with('Magento\Core\Helper\File\Storage')
            ->willReturn($storage);

        $contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $contextMock->expects($this->once())->method('getResponse')->willReturn($responseMock);
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($objectManager);

        $urlDecoderMock = $this->getMock('Magento\Framework\Url\DecoderInterface', [], [], '', false);
        $urlDecoderMock->expects($this->once())->method('decode')->with($decodedFile)->willReturn($file);

        $resultRawMock = $this->getMock('Magento\Framework\Controller\Result\Raw', [], [], '', false);
        $resultRawMock->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $resultRawMock->expects($this->at(1))->method('setHeader')->with('Pragma', 'public', true)->willReturnSelf();
        $resultRawMock->expects($this->at(2))->method('setHeader')
            ->with('Content-type', 'application/octet-stream', true)
            ->willReturnSelf();
        $resultRawMock->expects($this->at(3))->method('setHeader')
            ->with('Content-Length', $stat['size'])
            ->willReturnSelf();
        $resultRawMock->expects($this->at(4))->method('setHeader')
            ->with('Last-Modified', date('r', $stat['mtime']))
            ->willReturnSelf();

        $resultRawFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RawFactory',
            ['create'],
            [],
            '',
            false
        );
        $resultRawFactoryMock->expects($this->once())->method('create')->willReturn($resultRawMock);

        /** @var \Magento\Customer\Controller\Adminhtml\Index\Viewfile $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\Viewfile',
            [
                'context' => $contextMock,
                'urlDecoder' => $urlDecoderMock,
                'resultRawFactory' => $resultRawFactoryMock
            ]
        );
        $this->assertSame($resultRawMock, $controller->execute());
    }
}