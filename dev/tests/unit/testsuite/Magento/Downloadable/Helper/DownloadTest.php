<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Helper;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\App\Filesystem;
use Magento\Filesystem\File\ReadInterface as FileReadInterface;
use Magento\Filesystem\Directory\ReadInterface as DirReadInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;

/**
 * @runInSeparateProcess
 */
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

    /** @var bool Result of function_exists() */
    public static $functionExists;

    /** @var string Result of mime_content_type() */
    public static $mimeContentType;

    const FILE_SIZE = 4096;
    const FILE_PATH = '/some/path';
    const MIME_TYPE = 'image/png';
    const URL = 'http://example.com';

    public function setUp()
    {
        require_once __DIR__. '/../_files/download_mock.php';

        self::$functionExists = true;
        self::$mimeContentType = self::MIME_TYPE;

        $this->_filesystemMock = $this->getMock('Magento\App\Filesystem', [], [], '', false);
        $this->_handleMock = $this->getMock('Magento\Filesystem\File\ReadInterface', [], [], '', false);
        $this->_workingDirectoryMock = $this->getMock('Magento\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->_downloadableFileMock = $this->getMock('Magento\Downloadable\Helper\File', [], [], '', false);

        $this->_helper = new DownloadHelper(
            $this->getMock('Magento\App\Helper\Context', [], [], '', false),
            $this->getMock('Magento\Core\Helper\Data', [], [], '', false),
            $this->_downloadableFileMock,
            $this->getMock('Magento\Core\Helper\File\Storage\Database', [], [], '', false),
            $this->getMock('Magento\Core\Model\Store\Config', [], [], '', false),
            $this->_filesystemMock
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
     * @expectedException \Magento\Core\Exception
     * @exectedExceptionMessage Please set resource file and link type.
     */
    public function testGetFileSizeNoResource()
    {
        $this->_helper->getFileSize();
    }

    /**
     * @expectedException \Magento\Core\Exception
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
     * @expectedException \Magento\Core\Exception
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

        $this->_downloadableFileMock
            ->expects($this->once())
            ->method('getFileType')
            ->will($this->returnValue(self::MIME_TYPE));

        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
    }

    public function dataProviderForTestGetContentTypeThroughHelper()
    {
        return [
            [false, ''],
            [true, false]
        ];
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

        $this->_downloadableFileMock
            ->expects($this->any())
            ->method('ensureFileInFilesystem')
            ->with($path)
            ->will($this->returnValue($doesExist));

        $this->_workingDirectoryMock
            ->expects($doesExist ? $this->once() : $this->never())
            ->method('openFile')
            ->will($this->returnValue($this->_handleMock));

        $this->_filesystemMock
            ->expects($this->any())
            ->method('getDirectoryRead')
            ->with(Filesystem::MEDIA_DIR)
            ->will($this->returnValue($this->_workingDirectoryMock));

        $this->_helper->setResource($path, DownloadHelper::LINK_TYPE_FILE);
    }

    protected function _setupUrlMocks($size = self::FILE_SIZE, $url = self::URL, $additionalStatData = [])
    {
        $this->_handleMock
            ->expects($this->any())
            ->method('stat')
            ->will($this->returnValue(array_merge(['size' => $size, 'type' => self::MIME_TYPE], $additionalStatData)));

        $this->_filesystemMock
            ->expects($this->once())
            ->method('getRemoteResource')
            ->with($url)
            ->will($this->returnValue($this->_handleMock));

        $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
    }
}
