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

        /** @var \Magento\Customer\Controller\Adminhtml\Index\Viewfile $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\Viewfile',
            ['context' => $contextMock, 'urlDecoder' => $urlDecoderMock, 'resultRawFactory' => $resultRawFactoryMock]
        );
        $this->assertSame($resultRawMock, $controller->execute());
    }
}