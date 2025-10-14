<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirReadInterface;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadTest extends TestCase
{
    /** @var array Result of get_headers() function */
    public static $headers;

    /** @var DownloadHelper */
    protected $_helper;

    /** @var Filesystem|MockObject */
    protected $_filesystemMock;

    /** @var FileReadInterface|MockObject */
    protected $_handleMock;

    /** @var DirReadInterface|MockObject */
    protected $_workingDirectoryMock;

    /** @var DownloadableFile|MockObject */
    protected $_downloadableFileMock;

    /** @var  SessionManagerInterface|MockObject */
    protected $sessionManager;

    /** @var ReadFactory|MockObject */
    protected $fileReadFactory;

    /** @var bool Result of function_exists() */
    public static $functionExists;

    /** @var string Result of mime_content_type() */
    public static $mimeContentType;

    const FILE_SIZE = 4096;

    const FILE_PATH = '/some/path';

    const MIME_TYPE = 'image/png';

    const URL = 'http://example.com';

    /**
     * @var Mime|MockObject
     */
    private $mime;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../_files/download_mock.php';

        self::$functionExists = true;
        self::$mimeContentType = self::MIME_TYPE;

        $this->_filesystemMock = $this->createMock(Filesystem::class);
        $this->_handleMock = $this->createMock(\Magento\Framework\Filesystem\File\ReadInterface::class);
        $this->_workingDirectoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->_downloadableFileMock = $this->createMock(\Magento\Downloadable\Helper\File::class);
        $this->sessionManager = $this->getMockForAbstractClass(
            SessionManagerInterface::class
        );
        $this->fileReadFactory = $this->createMock(ReadFactory::class);
        $this->mime = $this->createMock(Mime::class);

        $this->_helper = (new ObjectManager($this))->getObject(
            \Magento\Downloadable\Helper\Download::class,
            [
                'downloadableFile' => $this->_downloadableFileMock,
                'filesystem'       => $this->_filesystemMock,
                'session'          => $this->sessionManager,
                'fileReadFactory'  => $this->fileReadFactory,
                'mime' => $this->mime
            ]
        );
    }

    public function testSetResourceInvalidPath()
    {
        $this->expectException('InvalidArgumentException');
        $this->_helper->setResource('/some/path/../file', DownloadHelper::LINK_TYPE_FILE);
    }

    public function testGetFileSizeNoResource()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please set resource file and link type.');
        $this->_helper->getFileSize();
    }

    public function testGetFileSizeInvalidLinkType()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid download link type.');
        $this->_helper->setResource(self::FILE_PATH, 'The link type is invalid. Verify and try again.');
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

    public function testGetFileSizeNoFile()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid download link type.');
        $this->_setupFileMocks(false);
        $this->_helper->getFileSize();
    }

    public function testGetContentType()
    {
        $this->mime->expects(
            self::once()
        )->method(
            'getMimeType'
        )->willReturn(
            self::MIME_TYPE
        );
        $this->_setupFileMocks();
        $this->_downloadableFileMock->expects($this->never())->method('getFileType');
        $this->_workingDirectoryMock->expects($this->once())->method('getAbsolutePath')
            ->willReturn('/path/to/file.txt');
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

        $this->mime->expects(
            self::once()
        )->method(
            'getMimeType'
        )->willReturn(
            self::MIME_TYPE
        );

        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
    }

    /**
     * @return array
     */
    public static function dataProviderForTestGetContentTypeThroughHelper()
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

    /**
     * @param bool $doesExist
     * @param int $size
     * @param string $path
     */
    protected function _setupFileMocks($doesExist = true, $size = self::FILE_SIZE, $path = self::FILE_PATH)
    {
        $this->_handleMock->expects($this->any())->method('stat')->willReturn(['size' => $size]);
        $this->_downloadableFileMock->expects($this->any())->method('ensureFileInFilesystem')->with($path)
            ->willReturn($doesExist);
        $this->_workingDirectoryMock->expects($doesExist ? $this->once() : $this->never())->method('openFile')
            ->willReturn($this->_handleMock);
        $this->_filesystemMock->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::MEDIA)
            ->willReturn($this->_workingDirectoryMock);
        $this->_helper->setResource($path, DownloadHelper::LINK_TYPE_FILE);
    }

    /**
     * @param int $size
     * @param string $url
     * @param array $additionalStatData
     */
    protected function _setupUrlMocks($size = self::FILE_SIZE, $url = self::URL, $additionalStatData = [])
    {
        $this->_handleMock->expects(
            $this->any()
        )->method(
            'stat'
        )->willReturn(
            array_merge(['size' => $size, 'type' => self::MIME_TYPE], $additionalStatData)
        );

        $this->fileReadFactory->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->_handleMock
        );

        self::$headers = ['200 OK'];
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
