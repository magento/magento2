<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use Magento\Framework\Exception\FileSystemException;
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
     * Test for getMetaData
     *
     * @dataProvider dataProviderForTestGetMetaData
     * @param string $path
     * @param array $expected
     * @param bool $isFileExist
     * @throws FileSystemException
     */
    public function testGetMetaData(string $path, array $expected, bool $isFileExist): void
    {
        $file = new File();
        if ($isFileExist) {
            $fileMetadata = $file->getMetadata($path);
            $this->assertIsArray($fileMetadata);
            $this->assertGetMetadata($expected, $fileMetadata);
        } else {
            $this->expectException(FileSystemException::class);
            $this->expectExceptionMessage('File \'' . $path . '\' doesn\'t exist');
            $file->getMetadata($path);
        }
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

    /**
     * Data provider for testGetMetaData
     *
     * @return array
     */
    public function dataProviderForTestGetMetaData(): array
    {
        return [
            'generic mime type' => [
                'path' => __DIR__ . '/File/_files/blank.html',
                'expected' => [
                    'path' => __DIR__ . '/File/_files',
                    'dirname' => __DIR__ . '/File',
                    'basename' => 'blank.html',
                    'extension' => 'html',
                    'filename' => 'blank.html',
                    'timestamp' => 1615451439,
                    'size' => 108,
                    'mimetype' => 'text/html',
                    'extra' => [
                        'image-width' => 0,
                        'image-height' => 0
                    ]
                ],
                'is a file exists' => true
            ],
            'javascript' => [
                'path' => __DIR__ . '/File/_files/javascript.js',
                'expected' => [
                    'path' => __DIR__ . '/File/_files',
                    'dirname' => __DIR__ . '/File',
                    'basename' => 'javascript.js',
                    'extension' => 'js',
                    'filename' => 'javascript.js',
                    'timestamp' => 1615451439,
                    'size' => 114,
                    'mimetype' => 'application/javascript',
                    'extra' => [
                        'image-width' => 0,
                        'image-height' => 0
                    ]
                ],
                'is a file exists' => true
            ],
            'tmp image' => [
                'path' => __DIR__ . '/File/_files/magento',
                'expected' => [
                    'path' => __DIR__ . '/File/_files',
                    'dirname' => __DIR__ . '/File',
                    'basename' => 'magento',
                    'extension' => '',
                    'filename' => 'magento',
                    'timestamp' => 1609751204,
                    'size' => 55303,
                    'mimetype' => 'image/jpeg',
                    'extra' => [
                        'image-width' => 1154,
                        'image-height' => 587
                    ]
                ],
                'is a file exists' => true
            ],
            'image' => [
                'path' => __DIR__ . '/File/_files/magento.jpg',
                'expected' => [
                    'path' => __DIR__ . '/File/_files',
                    'dirname' => __DIR__ . '/File',
                    'basename' => 'magento.jpg',
                    'extension' => 'jpg',
                    'filename' => 'magento.jpg',
                    'timestamp' => 1609751204,
                    'size' => 55303,
                    'mimetype' => 'image/jpeg',
                    'extra' => [
                        'image-width' => 1154,
                        'image-height' => 587
                    ]
                ],
                'is a file exists' => true
            ],
            'weird extension' => [
                    'path' => __DIR__ . '/File/_files/file.weird',
                    'expected' => [
                        'path' => __DIR__ . '/File/_files',
                        'dirname' => __DIR__ . '/File',
                        'basename' => 'file.weird',
                        'extension' => 'weird',
                        'filename' => 'file.weird',
                        'timestamp' => 1615451439,
                        'size' => 5,
                        'mimetype' => 'application/octet-stream',
                        'extra' => [
                            'image-width' => 0,
                            'image-height' => 0
                        ]
                    ],
                    'is a file exists' => true
                ],
            'weird uppercase extension' => [
                'path' => __DIR__ . '/File/_files/UPPERCASE.WEIRD',
                'expected' => [
                    'path' => __DIR__ . '/File/_files',
                    'dirname' => __DIR__ . '/File',
                    'basename' => 'UPPERCASE.WEIRD',
                    'extension' => 'WEIRD',
                    'filename' => 'UPPERCASE.WEIRD',
                    'timestamp' => 1615451439,
                    'size' => 5,
                    'mimetype' => 'application/octet-stream',
                    'extra' => [
                        'image-width' => 0,
                        'image-height' => 0
                    ]
                ],
                'is a file exists' => true
            ],
            'non-existent file' => [
                'path' =>  __DIR__ . '/File/_files/nonExistentFile.html',
                'expected' => [],
                'is a file exists' => false
            ],
            'directory' => [
                'path' => __DIR__ . '/File/_files/',
                'expected' => [],
                'is a file exists' => false
            ]
        ];
    }

    /**
     * Compares fileMetadata with the expected result.
     * We can't use assertEqual to compare arrays because we don't know the values of the file timestamp.
     * Timestamp is the time of the last modification of the file.
     *
     * @param array $expected
     * @param array $fileMetadata
     *
     * @return void
     */
    private function assertGetMetadata(array $expected, array $fileMetadata): void
    {
        foreach ($expected as $key => $item) {
            $this->assertArrayHasKey($key, $fileMetadata);
            if ($key === 'timestamp') {
                $this->assertIsInt($fileMetadata[$key]);
                continue;
            }
            $this->assertEquals($item, $fileMetadata[$key]);
        }
    }
}
