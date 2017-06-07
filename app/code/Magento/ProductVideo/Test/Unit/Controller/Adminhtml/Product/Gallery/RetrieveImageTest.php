<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Controller\Adminhtml\Product\Gallery;

/**
 * Class RetrieveImageTest
 */
class RetrieveImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Media\Config
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Image
     */
    protected $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\Adapter\Curl
     */
    protected $curlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $storageFileMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Image\Adapter\AbstractAdapter
     */
    protected $abstractAdapter;

    /**
     * @var \Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $image;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileDriverMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock = $this->getMock('\Magento\Backend\App\Action\Context', [], [], '', false);
        $this->validatorMock = $this
            ->getMock(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class, [], [], '', false);
        $this->rawFactoryMock =
            $this->getMock('\Magento\Framework\Controller\Result\RawFactory', ['create'], [], '', false);
        $response =
            $this->getMock(
                '\Magento\Framework\Controller\Result\Raw',
                [],
                [],
                '',
                false
            );
        $this->rawFactoryMock->expects($this->once())->method('create')->willReturn($response);
        $this->configMock = $this->getMock('\Magento\Catalog\Model\Product\Media\Config', [], [], '', false);
        $this->filesystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->adapterMock =
            $this->getMock('\Magento\Framework\Image', [], [], '', false);
        $this->adapterFactoryMock =
            $this->getMock('\Magento\Framework\Image\AdapterFactory', ['create'], [], '', false);
        $this->abstractAdapter = $this->getMock('\Magento\Framework\Image\Adapter\AbstractAdapter', [], [], '', false);
        $this->adapterFactoryMock->expects($this->once())->method('create')->willReturn($this->abstractAdapter);
        $this->curlMock = $this->getMock('\Magento\Framework\HTTP\Adapter\Curl', [], [], '', false);
        $this->storageFileMock =
            $this->getMock('\Magento\MediaStorage\Model\ResourceModel\File\Storage\File', [], [], '', false);
        $this->request = $this->getMock('\Magento\Framework\App\RequestInterface');
        $this->fileDriverMock = $this->getMock(\Magento\Framework\Filesystem\DriverInterface::class);

        $managerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $managerMock->expects($this->once())
            ->method('get')
            ->willReturn(new \Magento\Framework\Validator\AllowedProtocols());
        $this->contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($managerMock);

        $this->image = $objectManager->getObject(
            '\Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage',
            [
                'context' => $this->contextMock,
                'resultRawFactory' => $this->rawFactoryMock,
                'mediaConfig' => $this->configMock,
                'fileSystem' => $this->filesystemMock,
                'imageAdapterFactory' => $this->adapterFactoryMock,
                'curl' => $this->curlMock,
                'fileUtility' => $this->storageFileMock,
                'extensionValidator' => $this->validatorMock,
                'fileDriver' => $this->fileDriverMock,
            ]
        );
    }

    /**
     * Test execute()
     */
    public function testExecute()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(
            'https://example.com/test.jpg'
        );
        $readInterface = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->any())->method('validateUploadFile')->willReturn('true');
        $this->validatorMock->expects($this->once())->method('isValid')->with('jpg')->willReturn('true');
        $this->curlMock->expects($this->once())->method('read')->willReturn('testimage');

        $this->image->execute();
    }

    /**
     * Invalid file which is not an image should cause exception to be thrown.
     */
    public function testExecuteInvalidFileImage()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(
            'https://example.com/test.jpg'
        );
        $readInterface = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->any())
            ->method('validateUploadFile')
            ->willThrowException(new \Exception('Invalid File.'));
        $this->validatorMock->expects($this->once())->method('isValid')->with('jpg')->willReturn('true');
        $this->curlMock->expects($this->once())->method('read')->willReturn('testimage');
        $this->fileDriverMock->expects($this->once())->method('isExists')->willReturn('true');
        $this->fileDriverMock->expects($this->once())->method('deleteFile')->willReturn('false');

        $this->image->execute();
    }

    /**
     * Invalid file which is an invalid file type should cause exception to be thrown.
     */
    public function testExecuteInvalidFileType()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(
            'https://example.com/test.php'
        );
        $readInterface = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->never())->method('validateUploadFile');
        $this->validatorMock->expects($this->once())->method('isValid')->with('php')->willReturn(false);
        $this->fileDriverMock->expects($this->never())->method('isExists');

        $this->image->execute();
    }
}
