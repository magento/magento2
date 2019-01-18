<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use Magento\Framework\Filesystem\Driver\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /** @var string Result of file_get_contents() function */
    public static $fileGetContents;

    /** @var bool Result of file_put_contents() function */
    public static $filePutContents;

    public function setUp()
    {
        self::$fileGetContents = '';
        self::$filePutContents = true;
    }

    /**
     * @dataProvider dataProviderForTestGetAbsolutePath
     */
    public function testGetAbsolutePath($basePath, $path, $expected)
    {
        $file = new File();
        $this->assertEquals($expected, $file->getAbsolutePath($basePath, $path));
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetAbsolutePath()
    {
        return [
            ['/root/path/', 'sub', '/root/path/sub'],
            ['/root/path/', '/sub', '/root/path/sub'],
            ['/root/path/', '../sub', '/root/path/../sub'],
            ['/root/path/', '/root/path/sub', '/root/path/sub'],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetRelativePath
     */
    public function testGetRelativePath($basePath, $path, $expected)
    {
        $file = new File();
        $this->assertEquals($expected, $file->getRelativePath($basePath, $path));
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetRelativePath()
    {
        return [
            ['/root/path/', 'sub', 'sub'],
            ['/root/path/', '/sub', '/sub'],
            ['/root/path/', '/root/path/sub', 'sub'],
            ['/root/path/sub', '/root/path/other', '/root/path/other'],
        ];
    }
}
