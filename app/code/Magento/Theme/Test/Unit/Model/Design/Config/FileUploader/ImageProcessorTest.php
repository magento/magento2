<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config\FileUploader;

use Magento\Theme\Model\Design\Config\FileUploader\ImageProcessor;

class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uploaderFactory;

    /** @var \Magento\MediaStorage\Model\File\Uploader|\PHPUnit_Framework_MockObject_MockObject */
    protected $uploader;

    /** @var \Magento\Theme\Model\Design\Config\FileUploader\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $imageConfig;

    /** @var \Magento\Theme\Model\Design\BackendModelFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModelFactory;

    /** @var \Magento\Theme\Model\Design\Backend\Image|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModel;

    /** @var \Magento\Theme\Model\Design\Config\MetadataProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $metadataProvider;

    /** @var ImageProcessor */
    protected $imageProcessor;

    public function setUp()
    {
        $this->uploaderFactory = $this->getMockBuilder('Magento\MediaStorage\Model\File\UploaderFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploader = $this->getMockBuilder('Magento\MediaStorage\Model\File\Uploader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageConfig = $this->getMockBuilder('Magento\Theme\Model\Design\Config\FileUploader\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelFactory = $this->getMockBuilder('Magento\Theme\Model\Design\BackendModelFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModel = $this->getMockBuilder('Magento\Theme\Model\Design\Backend\Image')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataProvider = $this->getMockBuilder('Magento\Theme\Model\Design\Config\MetadataProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageProcessor = new ImageProcessor(
            $this->uploaderFactory,
            $this->imageConfig,
            $this->backendModelFactory,
            $this->metadataProvider
        );
    }

    public function testSaveToTmp()
    {
        $path = 'design/header/logo_src';
        $fieldCode = 'header_logo_src';
        $metadata = [
            $fieldCode => [
                'path' => $path,
                'backend_model' => 'Magento\Theme\Model\Design\Backend\Image'
            ],
        ];

        $this->metadataProvider->expects($this->once())
            ->method('get')
            ->willReturn($metadata);
        $this->backendModelFactory->expects($this->once())
            ->method('createByPath')
            ->with($path)
            ->willReturn($this->backendModel);
        $this->uploaderFactory->expects($this->once())
            ->method('create')
            ->with(['fileId' => $fieldCode])
            ->willReturn($this->uploader);
        $this->uploader->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true);
        $this->uploader->expects($this->once())
            ->method('setFilesDispersion')
            ->with(false);

        $this->backendModel->expects($this->once())
            ->method('getAllowedExtensions')
            ->willReturn(['png', 'jpg']);
        $this->uploader->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['png', 'jpg']);
        $this->uploader->expects($this->once())
            ->method('addValidateCallback')
            ->with('size', $this->backendModel, 'validateMaxSize');
        $this->imageConfig
            ->expects($this->once())
            ->method('getAbsoluteTmpMediaPath')
            ->willReturn('absolute/path/to/tmp/media');
        $this->uploader->expects($this->once())
            ->method('save')
            ->with('absolute/path/to/tmp/media')
            ->willReturn(['file' => 'file.jpg', 'size' => '234234']);
        $this->imageConfig->expects($this->once())
            ->method('getTmpMediaUrl')
            ->with('file.jpg')
            ->willReturn('http://magento2.com/pub/media/tmp/file.jpg');
        $this->assertEquals(
            [
                'file' => 'file.jpg',
                'size' => '234234',
                'url' => 'http://magento2.com/pub/media/tmp/file.jpg'
            ],
            $this->imageProcessor->saveToTmp($fieldCode)
        );
    }
}
