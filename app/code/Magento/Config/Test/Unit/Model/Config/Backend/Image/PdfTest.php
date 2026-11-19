<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend\Image;

use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Config\Model\Config\Backend\Image\Pdf;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase
{
    public function testBeforeSaveAllowsOnlyImageTypesSupportedByZendPdf(): void
    {
        $uploaderMock = $this->createPartialMock(Uploader::class, ['setAllowedExtensions', 'save']);
        $allowedExtensions = null;
        $uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->willReturnCallback(function (array $extensions) use (&$allowedExtensions, $uploaderMock) {
                $allowedExtensions = $extensions;
                return $uploaderMock;
            });
        $uploaderMock->method('save')->willReturn(['file' => 'logo.png']);

        $uploaderFactoryStub = $this->createStub(UploaderFactory::class);
        $uploaderFactoryStub->method('create')->willReturn($uploaderMock);

        $requestDataStub = $this->createStub(RequestDataInterface::class);
        $requestDataStub->method('getTmpName')->willReturn('/tmp/val');

        $filesystemStub = $this->createStub(Filesystem::class);
        $filesystemStub->method('getDirectoryWrite')->willReturn($this->createStub(WriteInterface::class));

        $model = (new ObjectManager($this))->getObject(
            Pdf::class,
            [
                'uploaderFactory' => $uploaderFactoryStub,
                'requestData' => $requestDataStub,
                'filesystem' => $filesystemStub,
            ]
        );
        $model->setFieldConfig(['upload_dir' => 'sales/store/logo']);
        $model->beforeSave();

        $this->assertSame(['tif', 'tiff', 'png', 'jpg', 'jpe', 'jpeg'], $allowedExtensions);
    }
}
