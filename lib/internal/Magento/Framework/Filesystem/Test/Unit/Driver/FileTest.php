<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /** @var string Result of file_get_contents() function */
    public static $fileGetContents;

    /** @var bool Result of file_put_contents() function */
    public static $filePutContents;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        self::$fileGetContents = '';
        self::$filePutContents = true;
    }

    /**
     * Test for getAbsolutePath method.
     *
     * @dataProvider dataProviderForTestGetAbsolutePath
     * @param string $basePath
     * @param string $path
     * @param string $expected
     */
    public function testGetAbsolutePath(string $basePath, string $path, string $expected)
    {
        $file = new File();
        $this->assertEquals($expected, $file->getAbsolutePath($basePath, $path));
    }

    /**
     * Data provider for testGetAbsolutePath.
     *
     * @return array
     */
    public function dataProviderForTestGetAbsolutePath(): array
    {
        return [
            ['/root/path/', 'sub', '/root/path/sub'],
            ['/root/path/', '/sub', '/root/path/sub'],
            ['/root/path/', '../sub', '/root/path/../sub'],
            ['/root/path/', '/root/path/sub', '/root/path/sub'],
        ];
    }

    /**
     * Test for getRelativePath method.
     *
     * @dataProvider dataProviderForTestGetRelativePath
     * @param string $basePath
     * @param string $path
     * @param string $expected
     */
    public function testGetRelativePath(string $basePath, string $path, string $expected)
    {
        $file = new File();
        $this->assertEquals($expected, $file->getRelativePath($basePath, $path));
    }

    /**
     * Data provider for testGetRelativePath.
     *
     * @return array
     */
    public function dataProviderForTestGetRelativePath(): array
    {
        return [
            ['/root/path/', 'sub', 'sub'],
            ['/root/path/', '/sub', '/sub'],
            ['/root/path/', '/root/path/sub', 'sub'],
            ['/root/path/sub', '/root/path/other', '/root/path/other'],
        ];
    }

    /**
     * Test for getRealPathSafety method.
     *
     * @dataProvider dataProviderForTestGetRealPathSafety
     * @param string $path
     * @param string $expected
     */
    public function testGetRealPathSafety(string $path, string $expected)
    {
        $file = new File();
        $this->assertEquals($expected, $file->getRealPathSafety($path));
    }

    /**
     * Data provider for testGetRealPathSafety;
     *
     * @return array
     */
    public function dataProviderForTestGetRealPathSafety(): array
    {
        return [
            ['/1/2/3', '/1/2/3'],
            ['/1/.test', '/1/.test'],
            ['/1/..test', '/1/..test'],
            ['/1/.test/.test', '/1/.test/.test'],
            ['/1/2/./.', '/1/2'],
            ['/1/2/./././', '/1/2'],
            ['/1/2/3/../..', '/1'],
            ['/1/2/3/.', '/1/2/3'],
            ['/1/2/3/./4/5', '/1/2/3/4/5'],
            ['/1/2/3/../4/5', '/1/2/4/5'],
            ['1/2/.//.\3/4/..\..\5', '1/2/5'],
            ['\./.test', '/.test'],
            ['\\1/\\\.\..test', '/1/..test'],
            ['/1/2\\3\\\.', '/1/2/3']
        ];
    }
}
