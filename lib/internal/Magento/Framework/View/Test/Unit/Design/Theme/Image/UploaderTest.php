<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for theme image uploader
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Image;

class UploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Image\Uploader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_transferAdapterMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_fileUploader;

    protected function setUp(): void
    {
        $this->_filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->_transferAdapterMock = $this->createMock(\Zend_File_Transfer_Adapter_Http::class);
        $this->_fileUploader = $this->createMock(\Magento\Framework\File\Uploader::class);

        $adapterFactory = $this->createMock(\Magento\Framework\HTTP\Adapter\FileTransferFactory::class);
        $adapterFactory->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->_transferAdapterMock
        );

        $uploaderFactory = $this->createPartialMock(\Magento\Framework\File\UploaderFactory::class, ['create']);
        $uploaderFactory->expects($this->any())->method('create')->willReturn($this->_fileUploader);

        $this->_model = new \Magento\Framework\View\Design\Theme\Image\Uploader(
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
                'exception' => \Magento\Framework\Exception\LocalizedException::class
            ],
            [
                'isUploaded' => true,
                'isValid' => true,
                'checkAllowedExtension' => false,
                'save' => true,
                'result' => false,
                'exception' => \Magento\Framework\Exception\LocalizedException::class
            ],
            [
                'isUploaded' => true,
                'isValid' => true,
                'checkAllowedExtension' => true,
                'save' => false,
                'result' => false,
                'exception' => \Magento\Framework\Exception\LocalizedException::class
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
