<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend\Image;

use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Config\Model\Config\Backend\Image\Logo;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LogoTest extends TestCase
{
    /**
     * @var Logo
     */
    private $model;

    /**
     * @var MockObject
     */
    private $uploaderFactoryMock;

    /**
     * @var MockObject
     */
    private $uploaderMock;

    /**
     * @var MockObject
     */
    private $requestDataMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderMock = $this->getMockBuilder(Uploader::class)
            ->setMethods(['setAllowedExtensions', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->uploaderMock);
        $this->requestDataMock = $this
            ->getMockBuilder(RequestDataInterface::class)
            ->setMethods(['getTmpName'])
            ->getMockForAbstractClass();
        $mediaDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
            ->getMock();
        $filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($mediaDirectoryMock);
        $this->model = $helper->getObject(
            Logo::class,
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
            ->willReturn('/tmp/val');
        $this->uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['jpg', 'jpeg', 'gif', 'png']);

        $this->uploaderMock->method('save')
            ->willReturn(['file' => 'filename']);

        $this->model->beforeSave();
    }
}
