<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Controller\Adminhtml\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RetrieveImageTest extends \PHPUnit\Framework\TestCase
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
    private $validatorMock;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileDriverMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->validatorMock = $this
            ->createMock(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $this->rawFactoryMock =
            $this->createPartialMock(\Magento\Framework\Controller\Result\RawFactory::class, ['create']);
        $response = new \Magento\Framework\DataObject();
        $this->rawFactoryMock->expects($this->once())->method('create')->willReturn($response);
        $this->configMock = $this->createMock(\Magento\Catalog\Model\Product\Media\Config::class);
        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->adapterMock =
            $this->createMock(\Magento\Framework\Image::class);
        $this->adapterFactoryMock =
            $this->createPartialMock(\Magento\Framework\Image\AdapterFactory::class, ['create']);
        $this->abstractAdapter = $this->createMock(\Magento\Framework\Image\Adapter\AbstractAdapter::class);
        $this->adapterFactoryMock->expects($this->once())->method('create')->willReturn($this->abstractAdapter);
        $this->curlMock = $this->createMock(\Magento\Framework\HTTP\Adapter\Curl::class);
        $this->storageFileMock = $this->createMock(\Magento\MediaStorage\Model\ResourceModel\File\Storage\File::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->request->expects($this->any())->method('isPost')->willReturn(true);
        $this->fileDriverMock = $this->createMock(\Magento\Framework\Filesystem\DriverInterface::class);
        $managerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($managerMock);

        $this->image = $objectManager->getObject(
            \Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage::class,
            [
                'context' => $this->contextMock,
                'resultRawFactory' => $this->rawFactoryMock,
                'mediaConfig' => $this->configMock,
                'fileSystem' => $this->filesystemMock,
                'imageAdapterFactory' => $this->adapterFactoryMock,
                'curl' => $this->curlMock,
                'fileUtility' => $this->storageFileMock,
                'protocolValidator' => new \Magento\Framework\Validator\AllowedProtocols(),
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
        $readInterface = $this->createMock(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class
        );
        $writeInterface = $this->createMock(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->any())->method('validateUploadFile')->willReturn('true');
        $this->validatorMock->expects($this->once())->method('isValid')->with('jpg')->willReturn('true');
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->willReturn($writeInterface);
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
        $readInterface = $this->createMock(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class,
            [],
            [],
            '',
            false
        );
        $writeInterface = $this->createMock(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->any())
            ->method('validateUploadFile')
            ->willThrowException(new \Exception('Invalid File.'));
        $this->validatorMock->expects($this->once())->method('isValid')->with('jpg')->willReturn('true');
        $this->curlMock->expects($this->once())->method('read')->willReturn('testimage');
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->willReturn($writeInterface);
        $writeInterface->expects($this->once())->method('isExist')->willReturn('true');
        $writeInterface->expects($this->once())->method('delete')->willReturn('false');

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
        $readInterface = $this->createMock(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class,
            [],
            [],
            '',
            false
        );
        $writeInterface = $this->createMock(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->never())->method('validateUploadFile');
        $this->validatorMock->expects($this->once())->method('isValid')->with('php')->willReturn(false);
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->willReturn($writeInterface);
        $writeInterface->expects($this->never())->method('isExist');

        $this->image->execute();
    }
}
