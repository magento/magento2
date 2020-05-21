<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\Http;
use PHPUnit\Framework\TestCase;

/**
 * Verify HttpTest class.
 */
class HttpTest extends TestCase
{
    /** @var array Result of get_headers() function */
    public static $headers;

    /** @var string Result of file_get_contents() function */
    public static $fileGetContents;

    /** @var bool Result of file_put_contents() function */
    public static $filePutContents;

    /** @var bool Result of fsockopen() function */
    public static $fsockopen;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        require_once __DIR__ . '/../_files/http_mock.php';

        self::$headers = [];
        self::$fileGetContents = '';
        self::$filePutContents = true;
        self::$fsockopen = true;
    }

    /**
     * Verify IsExists.
     *
     * @param string $status
     * @param bool $result
     * @dataProvider dataProviderForTestIsExists
     * @return void
     */
    public function testIsExists(string $status, bool $result): void
    {
        self::$headers = [$status];
        $this->assertEquals($result, (new Http())->isExists(''));
    }

    /**
     * Data provider fot test IsExists.
     *
     * @return array
     */
    public function dataProviderForTestIsExists(): array
    {
        return [['200 OK', true], ['404 Not Found', false]];
    }

    /**
     * Verify Stat.
     *
     * @param array $headers
     * @param array $result
     * @dataProvider dataProviderForTestStat
     * @return void
     */
    public function testStat(array $headers, array $result): void
    {
        self::$headers = $headers;
        $this->assertEquals($result, (new Http())->stat(''));
    }

    /**
     * Data provider for test Stat.
     *
     * @return array
     */
    public function dataProviderForTestStat(): array
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

    /**
     * Verify File get contents.
     *
     * @throws FileSystemException
     * @return void
     */
    public function testFileGetContents(): void
    {
        $content = 'some content';
        self::$fileGetContents = $content;
        $this->assertEquals($content, (new Http())->fileGetContents(''));
    }

    /**
     * Verify File get contents without content.
     *
     * @throws FileSystemException
     * @return void
     */
    public function testFileGetContentsNoContent(): void
    {
        $content = '';
        self::$fileGetContents = '';
        $this->assertEquals($content, (new Http())->fileGetContents(''));
    }

    /**
     * Verify File put contents.
     *
     * @throws FileSystemException
     * @return void
     */
    public function testFilePutContents(): void
    {
        self::$filePutContents = true;
        $this->assertTrue((new Http())->filePutContents('', ''));
    }

    /**
     * Verify file put contents without content.
     *
     * @throws FileSystemException
     * @return void
     */
    public function testFilePutContentsNoContent(): void
    {
        self::$filePutContents = 0;
        $this->assertEquals(0, (new Http())->filePutContents('', ''));
    }

    /**
     * Verify File put contents if is fail.
     *
     * @return void
     */
    public function testFilePutContentsFail(): void
    {
        $this->expectException('Magento\Framework\Exception\FileSystemException');
        self::$filePutContents = false;
        (new Http())->filePutContents('', '');
    }

    /**
     * Verify File open invalid url.
     *
     * @return void
     */
    public function testFileOpenInvalidUrl(): void
    {
        $this->expectException('Magento\Framework\Exception\FileSystemException');
        $this->expectExceptionMessage('The download URL is incorrect. Verify and try again.');
        (new Http())->fileOpen('', '');
    }

    /**
     * Verify File open.
     *
     * @throws FileSystemException
     * @return void
     */
    public function testFileOpen(): void
    {
        $fsockopenResult = 'resource';
        self::$fsockopen = $fsockopenResult;
        $this->assertEquals($fsockopenResult, (new Http())->fileOpen('example.com', 'r'));
    }
}
