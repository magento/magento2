<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
     *     * @param string $basePath
     * @param string $path
     * @param string $expected
     */
    #[DataProvider('dataProviderForTestGetAbsolutePath')]
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
    public static function dataProviderForTestGetAbsolutePath(): array
    {
        return [
            ['/root/path/', 'sub', '/root/path/sub'],
            ['/root/path/', '/sub', '/root/path/sub'],
            ['/root/path/', '../sub', '/root/path/../sub'],
            ['/root/path/', '/root/path/sub', '/root/path/sub'],
            ['', '', ''],
            ['0', '0', '0']
        ];
    }

    /**
     * Test for getRelativePath method.
     *     * @param string $basePath
     * @param string $path
     * @param string $expected
     */
    #[DataProvider('dataProviderForTestGetRelativePath')]
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
    public static function dataProviderForTestGetRelativePath(): array
    {
        return [
            ['/root/path/', 'sub', 'sub'],
            ['/root/path/', '/sub', '/sub'],
            ['/root/path/', '/root/path/sub', 'sub'],
            ['/root/path/sub', '/root/path/other', '/root/path/other'],
            ['/root/path/', '', ''],
            ['0', '0', '']
        ];
    }

    /**
     * Test for getRealPathSafety method.
     *     * @param string $path
     * @param string $expected
     */
    #[DataProvider('dataProviderForTestGetRealPathSafety')]
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
    public static function dataProviderForTestGetRealPathSafety(): array
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
