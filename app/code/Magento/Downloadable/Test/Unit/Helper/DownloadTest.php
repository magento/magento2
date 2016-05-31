<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirReadInterface;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    /** @var DownloadHelper */
    protected $_helper;

    /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $_filesystemMock;

    /** @var FileReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_handleMock;

    /** @var DirReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_workingDirectoryMock;

    /** @var DownloadableFile|\PHPUnit_Framework_MockObject_MockObject */
    protected $_downloadableFileMock;

    /** @var  \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionManager;

    /** @var \Magento\Framework\Filesystem\File\ReadFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileReadFactory;

    /** @var bool Result of function_exists() */
    public static $functionExists;

    /** @var string Result of mime_content_type() */
    public static $mimeContentType;

    const FILE_SIZE = 4096;

    const FILE_PATH = '/some/path';

    const MIME_TYPE = 'image/png';

    const URL = 'http://example.com';

    protected function setUp()
    {
        require_once __DIR__ . '/../_files/download_mock.php';

        self::$functionExists = true;
        self::$mimeContentType = self::MIME_TYPE;

        $this->_filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->_handleMock = $this->getMock(
            'Magento\Framework\Filesystem\File\ReadInterface',
            [],
            [],
            '',
            false
        );
        $this->_workingDirectoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\ReadInterface',
            [],
            [],
            '',
            false
        );
        $this->_downloadableFileMock = $this->getMock('Magento\Downloadable\Helper\File', [], [], '', false);
        $this->sessionManager = $this->getMockForAbstractClass('Magento\Framework\Session\SessionManagerInterface');
        $this->fileReadFactory = $this->getMock('Magento\Framework\Filesystem\File\ReadFactory', [], [], '', false);

        $this->_helper = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Downloadable\Helper\Download',
            [
                'downloadableFile' => $this->_downloadableFileMock,
                'filesystem'       => $this->_filesystemMock,
                'session'          => $this->sessionManager,
                'fileReadFactory'  => $this->fileReadFactory,
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetResourceInvalidPath()
    {
        $this->_helper->setResource('/some/path/../file', DownloadHelper::LINK_TYPE_FILE);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @exectedExceptionMessage Please set resource file and link type.
     */
    public function testGetFileSizeNoResource()
    {
        $this->_helper->getFileSize();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid download link type.
     */
    public function testGetFileSizeInvalidLinkType()
    {
        $this->_helper->setResource(self::FILE_PATH, 'invalid link type');
        $this->_helper->getFileSize();
    }

    public function testGetFileSizeUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals(self::FILE_SIZE, $this->_helper->getFileSize());
    }

    public function testGetFileSize()
    {
        $this->_setupFileMocks();
        $this->assertEquals(self::FILE_SIZE, $this->_helper->getFileSize());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid download link type.
     */
    public function testGetFileSizeNoFile()
    {
        $this->_setupFileMocks(false);
        $this->_helper->getFileSize();
    }

    public function testGetContentType()
    {
        $this->_setupFileMocks();
        $this->_downloadableFileMock->expects($this->never())->method('getFileType');
        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
    }

    /**
     * @dataProvider dataProviderForTestGetContentTypeThroughHelper
     */
    public function testGetContentTypeThroughHelper($functionExistsResult, $mimeContentTypeResult)
    {
        $this->_setupFileMocks();
        self::$functionExists = $functionExistsResult;
        self::$mimeContentType = $mimeContentTypeResult;

        $this->_downloadableFileMock->expects(
            $this->once()
        )->method(
            'getFileType'
        )->will(
            $this->returnValue(self::MIME_TYPE)
        );

        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
    }

    public function dataProviderForTestGetContentTypeThroughHelper()
    {
        return [[false, ''], [true, false]];
    }

    public function testGetContentTypeUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
    }

    public function testGetFilename()
    {
        $baseName = 'base_name.file';
        $path = TESTS_TEMP_DIR . '/' . $baseName;
        $this->_setupFileMocks(true, self::FILE_SIZE, $path);
        $this->assertEquals($baseName, $this->_helper->getFilename());
    }

    public function testGetFileNameUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals('example.com', $this->_helper->getFilename());
    }

    public function testGetFileNameUrlWithContentDisposition()
    {
        $fileName = 'some_other.file';
        $this->_setupUrlMocks(self::FILE_SIZE, self::URL, ['disposition' => "inline; filename={$fileName}"]);
        $this->assertEquals($fileName, $this->_helper->getFilename());
    }

    protected function _setupFileMocks($doesExist = true, $size = self::FILE_SIZE, $path = self::FILE_PATH)
    {
        $this->_handleMock->expects($this->any())->method('stat')->will($this->returnValue(['size' => $size]));
        $this->_downloadableFileMock->expects($this->any())->method('ensureFileInFilesystem')->with($path)
            ->will($this->returnValue($doesExist));
        $this->_workingDirectoryMock->expects($doesExist ? $this->once() : $this->never())->method('openFile')
            ->will($this->returnValue($this->_handleMock));
        $this->_filesystemMock->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::MEDIA)
            ->will($this->returnValue($this->_workingDirectoryMock));
        $this->_helper->setResource($path, DownloadHelper::LINK_TYPE_FILE);
    }

    protected function _setupUrlMocks($size = self::FILE_SIZE, $url = self::URL, $additionalStatData = [])
    {
        $this->_handleMock->expects(
            $this->any()
        )->method(
            'stat'
        )->will(
            $this->returnValue(array_merge(['size' => $size, 'type' => self::MIME_TYPE], $additionalStatData))
        );

        $this->fileReadFactory->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_handleMock)
        );

        $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
    }

    public function testOutput()
    {
        $this->sessionManager
            ->expects($this->once())->method('writeClose');
        $this->_setupUrlMocks(self::FILE_SIZE, self::URL, ['disposition' => "inline; filename=test.txt"]);
        $this->_helper->output();
    }
}
