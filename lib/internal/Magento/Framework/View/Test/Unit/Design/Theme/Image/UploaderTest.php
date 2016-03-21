<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for theme image uploader
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Image;

class UploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Image\Uploader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transferAdapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileUploader;

    protected function setUp()
    {
        $this->_filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->_transferAdapterMock = $this->getMock('Zend_File_Transfer_Adapter_Http', [], [], '', false);
        $this->_fileUploader = $this->getMock('Magento\Framework\File\Uploader', [], [], '', false);

        $adapterFactory = $this->getMock('Magento\Framework\HTTP\Adapter\FileTransferFactory');
        $adapterFactory->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_transferAdapterMock)
        );

        $uploaderFactory = $this->getMock(
            'Magento\Framework\File\UploaderFactory',
            ['create'],
            [],
            '',
            false
        );
        $uploaderFactory->expects($this->any())->method('create')->will($this->returnValue($this->_fileUploader));

        $this->_model = new \Magento\Framework\View\Design\Theme\Image\Uploader(
            $this->_filesystemMock,
            $adapterFactory,
            $uploaderFactory
        );
    }

    protected function tearDown()
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
                'exception' => 'Magento\Framework\Exception\LocalizedException'
            ],
            [
                'isUploaded' => true,
                'isValid' => true,
                'checkAllowedExtension' => false,
                'save' => true,
                'result' => false,
                'exception' => 'Magento\Framework\Exception\LocalizedException'
            ],
            [
                'isUploaded' => true,
                'isValid' => true,
                'checkAllowedExtension' => true,
                'save' => false,
                'result' => false,
                'exception' => 'Magento\Framework\Exception\LocalizedException'
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
            $this->setExpectedException($exception);
        }
        $testScope = 'scope';
        $this->_transferAdapterMock->expects(
            $this->any()
        )->method(
            'isUploaded'
        )->with(
            $testScope
        )->will(
            $this->returnValue($isUploaded)
        );
        $this->_transferAdapterMock->expects(
            $this->any()
        )->method(
            'isValid'
        )->with(
            $testScope
        )->will(
            $this->returnValue($isValid)
        );
        $this->_fileUploader->expects(
            $this->any()
        )->method(
            'checkAllowedExtension'
        )->will(
            $this->returnValue($checkExtension)
        );
        $this->_fileUploader->expects($this->any())->method('save')->will($this->returnValue($save));
        $this->_fileUploader->expects(
            $this->any()
        )->method(
            'getUploadedFileName'
        )->will(
            $this->returnValue('test_filename')
        );

        $this->assertEquals($result, $this->_model->uploadPreviewImage($testScope, '/tmp'));
    }
}
