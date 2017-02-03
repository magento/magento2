<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for uploader service
 */
namespace Magento\Theme\Test\Unit\Model\Uploader;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Theme\Model\Uploader\Service
     */
    protected $_service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Model\File\Uploader
     */
    protected $_uploader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\File\Size
     */
    protected $_fileSizeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected $_filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Read
     */
    protected $_directoryMock;

    /**
     * @var int
     */
    const MB_MULTIPLIER = 1048576;

    protected function setUp()
    {
        $this->_uploader = $this->getMock('Magento\MediaStorage\Model\File\Uploader', [], [], '', false);
        $this->_uploaderFactory = $this->getMock(
            'Magento\MediaStorage\Model\File\UploaderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_uploaderFactory->expects($this->any())->method('create')->will($this->returnValue($this->_uploader));
        $this->_directoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            [],
            [],
            '',
            false
        );
        $this->_filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->_filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryRead'
        )->will(
            $this->returnValue($this->_directoryMock)
        );
        /** @var $service \Magento\Theme\Model\Uploader\Service */

        $this->_fileSizeMock = $this->getMockBuilder(
            'Magento\Framework\File\Size'
        )->setMethods(
            ['getMaxFileSize']
        )->disableOriginalConstructor()->getMock();

        $this->_fileSizeMock->expects(
            $this->any()
        )->method(
            'getMaxFileSize'
        )->will(
            $this->returnValue(600 * self::MB_MULTIPLIER)
        );
    }

    protected function tearDown()
    {
        $this->_service = null;
        $this->_uploader = null;
        $this->_fileSizeMock = null;
        $this->_filesystemMock = null;
        $this->_uploaderFactory = null;
    }

    public function testUploadLimitNotConfigured()
    {
        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory
        );
        $this->assertEquals(600 * self::MB_MULTIPLIER, $this->_service->getJsUploadMaxSize());
        $this->assertEquals(600 * self::MB_MULTIPLIER, $this->_service->getCssUploadMaxSize());
    }

    public function testGetCssUploadMaxSize()
    {
        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory,
            ['css' => '5M']
        );
        $this->assertEquals(5 * self::MB_MULTIPLIER, $this->_service->getCssUploadMaxSize());
    }

    public function testGetJsUploadMaxSize()
    {
        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory,
            ['js' => '3M']
        );
        $this->assertEquals(3 * self::MB_MULTIPLIER, $this->_service->getJsUploadMaxSize());
    }

    public function testGetFileContent()
    {
        $fileName = 'file.name';

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'getRelativePath'
        )->with(
            $fileName
        )->will(
            $this->returnValue($fileName)
        );

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $fileName
        )->will(
            $this->returnValue('content from my file')
        );

        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory,
            ['js' => '3M']
        );

        $this->assertEquals('content from my file', $this->_service->getFileContent($fileName));
    }

    public function testUploadCssFile()
    {
        $fileName = 'file.name';
        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory,
            ['css' => '3M']
        );
        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'getRelativePath'
        )->with(
            $fileName
        )->will(
            $this->returnValue($fileName)
        );

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $fileName
        )->will(
            $this->returnValue('content')
        );

        $this->_uploader->expects(
            $this->once()
        )->method(
            'validateFile'
        )->will(
            $this->returnValue(['name' => $fileName, 'tmp_name' => $fileName])
        );

        $this->assertEquals(
            ['content' => 'content', 'filename' => $fileName],
            $this->_service->uploadCssFile($fileName)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testUploadInvalidCssFile()
    {
        $fileName = 'file.name';

        $this->_uploader->expects(
            $this->once()
        )->method(
            'getFileSize'
        )->will(
            $this->returnValue(30 * self::MB_MULTIPLIER)
        );

        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory,
            ['css' => '10M']
        );

        $this->_service->uploadCssFile($fileName);
    }

    public function testUploadJsFile()
    {
        $fileName = 'file.name';

        $this->_fileSizeMock->expects(
            $this->once()
        )->method(
            'getMaxFileSize'
        )->will(
            $this->returnValue(600 * self::MB_MULTIPLIER)
        );

        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory,
            ['js' => '500M']
        );
        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'getRelativePath'
        )->with(
            $fileName
        )->will(
            $this->returnValue($fileName)
        );

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $fileName
        )->will(
            $this->returnValue('content')
        );

        $this->_uploader->expects(
            $this->once()
        )->method(
            'validateFile'
        )->will(
            $this->returnValue(['name' => $fileName, 'tmp_name' => $fileName])
        );

        $this->_uploader->expects($this->once())->method('getFileSize')->will($this->returnValue('499'));

        $this->assertEquals(
            ['content' => 'content', 'filename' => $fileName],
            $this->_service->uploadJsFile($fileName)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testUploadInvalidJsFile()
    {
        $fileName = 'file.name';
        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->_uploaderFactory,
            ['js' => '100M']
        );

        $this->_uploader->expects(
            $this->once()
        )->method(
            'getFileSize'
        )->will(
            $this->returnValue(499 * self::MB_MULTIPLIER)
        );

        $this->_service->uploadJsFile($fileName);
    }
}
