<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Controller\Adminhtml\Product\Gallery;

/**
 * Class RetrieveImageTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock = $this->getMock(\Magento\Backend\App\Action\Context::class, [], [], '', false);
        $this->rawFactoryMock =
            $this->getMock(\Magento\Framework\Controller\Result\RawFactory::class, ['create'], [], '', false);
        $response = new \Magento\Framework\DataObject();
        $this->rawFactoryMock->expects($this->once())->method('create')->willReturn($response);
        $this->configMock = $this->getMock(\Magento\Catalog\Model\Product\Media\Config::class, [], [], '', false);
        $this->filesystemMock = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->adapterMock =
            $this->getMock(\Magento\Framework\Image::class, [], [], '', false);
        $this->adapterFactoryMock =
            $this->getMock(\Magento\Framework\Image\AdapterFactory::class, ['create'], [], '', false);
        $this->abstractAdapter = $this->getMock(
            \Magento\Framework\Image\Adapter\AbstractAdapter::class,
            [],
            [],
            '',
            false
        );
        $this->adapterFactoryMock->expects($this->once())->method('create')->willReturn($this->abstractAdapter);
        $this->curlMock = $this->getMock(\Magento\Framework\HTTP\Adapter\Curl::class, [], [], '', false);
        $this->storageFileMock =
        $this->getMock(\Magento\MediaStorage\Model\ResourceModel\File\Storage\File::class, [], [], '', false);
        $this->request = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $this->contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $managerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
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
            ]
        );
    }

    /**
     * Test execute()
     */
    public function testExecute()
    {
        $this->request->expects($this->any())->method('getParam')->will(
            $this->returnValueMap(
                ['remote_image' => 'https://pp.vk.me/c304605/v304605289/3ff9/s4rpaW_TZ6A.jpg']
            )
        );
        $readInterface = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('/var/www/application/sample.jpg');
        $this->abstractAdapter->expects($this->any())->method('validateUploadFile')->willReturn('true');

        $this->image->execute();
    }
}
