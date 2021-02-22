<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for uploader service
 */
namespace Magento\Theme\Test\Unit\Model\Uploader;

use Magento\Framework\Convert\DataSize;

class ServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Theme\Model\Uploader\Service
     */
    protected $_service;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\MediaStorage\Model\File\Uploader
     */
    protected $_uploader;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\File\Size
     */
    protected $_fileSizeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Convert\DataSize
     */
    protected $dataSize;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem
     */
    protected $_filesystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\Read
     */
    protected $_directoryMock;

    /**
     * @var int
     */
    const MB_MULTIPLIER = 1048576;

    protected function setUp(): void
    {
        $this->_uploader = $this->createMock(\Magento\MediaStorage\Model\File\Uploader::class);
        $this->dataSize = new DataSize();
        $this->_uploaderFactory = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\UploaderFactory::class,
            ['create']
        );
        $this->_uploaderFactory->expects($this->any())->method('create')->willReturn($this->_uploader);
        $this->_directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $this->_filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->_filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryRead'
        )->willReturn(
            $this->_directoryMock
        );
        /** @var $service \Magento\Theme\Model\Uploader\Service */

        $this->_fileSizeMock = $this->getMockBuilder(
            \Magento\Framework\File\Size::class
        )->setMethods(
            ['getMaxFileSize']
        )->disableOriginalConstructor()->getMock();

        $this->_fileSizeMock->expects(
            $this->any()
        )->method(
            'getMaxFileSize'
        )->willReturn(
            600 * self::MB_MULTIPLIER
        );
    }

    protected function tearDown(): void
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
            $this->dataSize,
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
            $this->dataSize,
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
            $this->dataSize,
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
        )->willReturn(
            $fileName
        );

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $fileName
        )->willReturn(
            'content from my file'
        );

        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->dataSize,
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
            $this->dataSize,
            $this->_uploaderFactory,
            ['css' => '3M']
        );
        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'getRelativePath'
        )->with(
            $fileName
        )->willReturn(
            $fileName
        );

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $fileName
        )->willReturn(
            'content'
        );

        $this->_uploader->expects(
            $this->once()
        )->method(
            'validateFile'
        )->willReturn(
            ['name' => $fileName, 'tmp_name' => $fileName]
        );

        $this->assertEquals(
            ['content' => 'content', 'filename' => $fileName],
            $this->_service->uploadCssFile($fileName)
        );
    }

    /**
     */
    public function testUploadInvalidCssFile()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $fileName = 'file.name';

        $this->_uploader->expects(
            $this->once()
        )->method(
            'getFileSize'
        )->willReturn(
            30 * self::MB_MULTIPLIER
        );

        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->dataSize,
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
        )->willReturn(
            600 * self::MB_MULTIPLIER
        );

        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->dataSize,
            $this->_uploaderFactory,
            ['js' => '500M']
        );
        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'getRelativePath'
        )->with(
            $fileName
        )->willReturn(
            $fileName
        );

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $fileName
        )->willReturn(
            'content'
        );

        $this->_uploader->expects(
            $this->once()
        )->method(
            'validateFile'
        )->willReturn(
            ['name' => $fileName, 'tmp_name' => $fileName]
        );

        $this->_uploader->expects($this->once())->method('getFileSize')->willReturn('499');

        $this->assertEquals(
            ['content' => 'content', 'filename' => $fileName],
            $this->_service->uploadJsFile($fileName)
        );
    }

    /**
     */
    public function testUploadInvalidJsFile()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $fileName = 'file.name';
        $this->_service = new \Magento\Theme\Model\Uploader\Service(
            $this->_filesystemMock,
            $this->_fileSizeMock,
            $this->dataSize,
            $this->_uploaderFactory,
            ['js' => '100M']
        );

        $this->_uploader->expects(
            $this->once()
        )->method(
            'getFileSize'
        )->willReturn(
            499 * self::MB_MULTIPLIER
        );

        $this->_service->uploadJsFile($fileName);
    }
}
