<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for theme image uploader
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Image;

use Magento\Framework\File\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\View\Design\Theme\Image\Uploader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UploaderTest extends TestCase
{
    /**
     * @var Uploader|MockObject
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_helperMock;

    /**
     * @var MockObject
     */
    protected $_filesystemMock;

    /**
     * @var MockObject
     */
    protected $_transferAdapterMock;

    /**
     * @var MockObject
     */
    protected $_fileUploader;

    protected function setUp(): void
    {
        $this->_filesystemMock = $this->createMock(Filesystem::class);
        $this->_transferAdapterMock = $this->createMock(Http::class);
        $this->_fileUploader = $this->createMock(\Magento\Framework\File\Uploader::class);

        $adapterFactory = $this->createMock(FileTransferFactory::class);
        $adapterFactory->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->_transferAdapterMock
        );

        $uploaderFactory = $this->createPartialMock(UploaderFactory::class, ['create']);
        $uploaderFactory->expects($this->any())->method('create')->willReturn($this->_fileUploader);

        $this->_model = new Uploader(
            $this->_filesystemMock,
            $adapterFactory,
            $uploaderFactory
        );
    }

    protected function tearDown(): void
    {
        $this->_model = null;
        $this->_transferAdapterMock = null;
        $this->_fileUploader = null;
    }

    /**
     * @return array
     */
    public function uploadDataProvider()
    {
        return [
            [
                'isUploaded' => true,
                'isValid' => true,
                'checkAllowedExtension' => true,
                'save' => true,
                'result' => '/tmp/test_filename',
                'exception' => null,
            ],
            [
                'isUploaded' => false,
                'isValid' => true,
                'checkAllowedExtension' => true,
                'save' => true,
                'result' => false,
                'exception' => null
            ],
            [
                'isUploaded' => true,
                'isValid' => false,
                'checkAllowedExtension' => true,
                'save' => true,
                'result' => false,
                'exception' => LocalizedException::class
            ],
            [
                'isUploaded' => true,
                'isValid' => true,
                'checkAllowedExtension' => false,
                'save' => true,
                'result' => false,
                'exception' => LocalizedException::class
            ],
            [
                'isUploaded' => true,
                'isValid' => true,
                'checkAllowedExtension' => true,
                'save' => false,
                'result' => false,
                'exception' => LocalizedException::class
            ]
        ];
    }

    /**
     * @dataProvider uploadDataProvider
     * @covers \Magento\Framework\View\Design\Theme\Image\Uploader::uploadPreviewImage
     */
    public function testUploadPreviewImage($isUploaded, $isValid, $checkExtension, $save, $result, $exception)
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $testScope = 'scope';
        $this->_transferAdapterMock->expects(
            $this->any()
        )->method(
            'isUploaded'
        )->with(
            $testScope
        )->willReturn(
            $isUploaded
        );
        $this->_transferAdapterMock->expects(
            $this->any()
        )->method(
            'isValid'
        )->with(
            $testScope
        )->willReturn(
            $isValid
        );
        $this->_fileUploader->expects(
            $this->any()
        )->method(
            'checkAllowedExtension'
        )->willReturn(
            $checkExtension
        );
        $this->_fileUploader->expects($this->any())->method('save')->willReturn($save);
        $this->_fileUploader->expects(
            $this->any()
        )->method(
            'getUploadedFileName'
        )->willReturn(
            'test_filename'
        );

        $this->assertEquals($result, $this->_model->uploadPreviewImage($testScope, '/tmp'));
    }
}
