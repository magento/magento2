<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use \Magento\Framework\Filesystem\Driver\Http;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /** @var array Result of get_headers() function */
    public static $headers;

    /** @var string Result of file_get_contents() function */
    public static $fileGetContents;

    /** @var bool Result of file_put_contents() function */
    public static $filePutContents;

    /** @var bool Result of fsockopen() function */
    public static $fsockopen;

    protected function setUp()
    {
        require_once __DIR__ . '/../_files/http_mock.php';

        self::$headers = [];
        self::$fileGetContents = '';
        self::$filePutContents = true;
        self::$fsockopen = true;
    }

    /**
     * @dataProvider dataProviderForTestIsExists
     */
    public function testIsExists($status, $result)
    {
        self::$headers = [$status];
        $this->assertEquals($result, (new Http())->isExists(''));
    }

    public function dataProviderForTestIsExists()
    {
        return [['200 OK', true], ['404 Not Found', false]];
    }

    /**
     * @dataProvider dataProviderForTestStat
     */
    public function testStat($headers, $result)
    {
        self::$headers = $headers;
        $this->assertEquals($result, (new Http())->stat(''));
    }

    public function dataProviderForTestStat()
    {
        $headers1 = [
            'Content-Length' => 128,
            'Content-Type' => 'type',
            'Last-Modified' => '2013-12-19T17:41:45+00:00',
            'Content-Disposition' => 1024,
        ];

        $result1 = $this->_resultForStat(
            ['size' => 128, 'type' => 'type', 'mtime' => '2013-12-19T17:41:45+00:00', 'disposition' => 1024]
        );

        return [[[], $this->_resultForStat()], [$headers1, $result1]];
    }

    /**
     * Form a result array similar to what stat() produces
     *
     * @param array $nonEmptyValues
     * @return array
     */
    protected function _resultForStat($nonEmptyValues = [])
    {
        $result = [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'atime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0,
            'size' => 0,
            'type' => '',
            'mtime' => 0,
            'disposition' => null,
        ];

        return array_merge($result, $nonEmptyValues);
    }

    public function testFileGetContents()
    {
        $content = 'some content';
        self::$fileGetContents = $content;
        $this->assertEquals($content, (new Http())->fileGetContents(''));
    }

    public function testFileGetContentsNoContent()
    {
        $content = '';
        self::$fileGetContents = '';
        $this->assertEquals($content, (new Http())->fileGetContents(''));
    }

    public function testFilePutContents()
    {
        self::$filePutContents = true;
        $this->assertTrue((new Http())->filePutContents('', ''));
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testFilePutContentsFail()
    {
        self::$filePutContents = false;
        (new Http())->filePutContents('', '');
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     * @expectedExceptionMessage Please correct the download URL.
     */
    public function testFileOpenInvalidUrl()
    {
        (new Http())->fileOpen('', '');
    }

    public function testFileOpen()
    {
        $fsockopenResult = 'resource';
        self::$fsockopen = $fsockopenResult;
        $this->assertEquals($fsockopenResult, (new Http())->fileOpen('example.com', 'r'));
    }
}
