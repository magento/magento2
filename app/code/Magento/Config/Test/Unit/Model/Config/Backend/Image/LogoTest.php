<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Backend\Image;

class LogoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Backend\Image\Logo
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestDataMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->uploaderFactoryMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\UploaderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Uploader::class)
            ->setMethods(['setAllowedExtensions', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->uploaderMock));
        $this->requestDataMock = $this
            ->getMockBuilder(\Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface::class)
            ->setMethods(['getTmpName'])
            ->getMockForAbstractClass();
        $mediaDirectoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
            ->getMock();
        $filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($mediaDirectoryMock));
        $this->model = $helper->getObject(
            \Magento\Config\Model\Config\Backend\Image\Logo::class,
            [
                'uploaderFactory' => $this->uploaderFactoryMock,
                'requestData' => $this->requestDataMock,
                'filesystem' => $filesystemMock,
            ]
        );
    }

    public function testBeforeSave()
    {
        $this->requestDataMock->expects($this->once())
            ->method('getTmpName')
            ->will($this->returnValue('/tmp/val'));
        $this->uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->with($this->equalTo(['jpg', 'jpeg', 'gif', 'png']));
        $this->model->beforeSave();
    }
}
