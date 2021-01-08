<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Gallery;

class UploadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|MockObject
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|MockObject
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|MockObject
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory|MockObject
     */
    private $adapterFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config|MockObject
     */
    private $productMediaConfig;

    /**
     * @var \Magento\Backend\Model\Image\UploadResizeConfigInterface|MockObject
     */
    private $imageUploadConfig;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resultRawFactory = $this->createMock(
            \Magento\Framework\Controller\Result\RawFactory::class
        );
        $this->uploaderFactory = $this->createMock(
            \Magento\MediaStorage\Model\File\UploaderFactory::class
        );
        $filesystem = $this->createMock(
            \Magento\Framework\Filesystem::class
        );
        $this->mediaDirectory = $this->createMock(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn(
            $this->mediaDirectory
        );
        $this->adapterFactory = $this->createMock(
            \Magento\Framework\Image\AdapterFactory::class
        );
        $this->productMediaConfig = $this->createMock(
            \Magento\Catalog\Model\Product\Media\Config::class
        );
        $this->imageUploadConfig = $this->createMock(
            \Magento\Backend\Model\Image\UploadResizeConfigInterface::class
        );
        $this->baseTmpPath = 'base/tmp/';
        $this->basePath =  'base/real/';
        $this->url = 'http://local.magento2.com/media/tmp/catalog/product/f/i/file.png';
        $this->allowedExtensions = [
            'jpg' => 'image/jpg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/png',
            'png' => 'image/gif'
        ];
        $this->result= [
            'file' => 'file.png.tmp',
            'url' => $this->url
        ];
        $this->fileId = 'file.png';
        $this->upload = $objectManager->getObject(
            \Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload::class,
            [
                'resultRawFactory' => $this->resultRawFactory,
                'uploaderFactory' => $this->uploaderFactory,
                'mediaDirectory' => $this->mediaDirectory,
                'adapterFactory' => $this->adapterFactory,
                'productMediaConfig' => $this->productMediaConfig,
                'imageUploadConfig' => $this->imageUploadConfig
            ]
        );
    }

    public function testExecute()
    {
        $uploader = $this->createMock(\Magento\MediaStorage\Model\File\Uploader::class);
        $this->uploaderFactory->expects($this->once())->method('create')->willReturn($uploader);
        $uploader->expects($this->once())->method('setAllowedExtensions')->with(array_keys($this->allowedExtensions));
        $uploader->expects($this->once())->method('setAllowRenameFiles')->with(true);
        $this->productMediaConfig->expects($this->once())->method('getBaseTmpMediaPath')->willReturn($this->baseTmpPath);
        $this->mediaDirectory->expects($this->once())->method('getAbsolutePath')->with($this->baseTmpPath)
            ->willReturn($this->basePath);
        $uploader->expects($this->once())->method('save')->with($this->basePath)
            ->willReturn(['tmp_name' => $this->baseTmpPath, 'file' => $this->fileId, 'path' => $this->basePath]);
        $this->productMediaConfig->expects($this->once())->method('getTmpMediaUrl')->willReturn($this->url);
        $response = $this->createMock(\Magento\Framework\Controller\Result\Raw::class);
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($response);
        $response->expects($this->once())->method('setHeader')->with('Content-type', 'text/plain');
        $response->expects($this->once())->method('setContents')->with(json_encode($this->result));
        $this->upload->execute();
    }
}
