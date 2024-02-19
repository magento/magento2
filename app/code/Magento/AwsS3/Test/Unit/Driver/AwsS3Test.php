<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Unit\Driver;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToRetrieveMetadata;
use Magento\AwsS3\Driver\AwsS3;
use Magento\Framework\Exception\FileSystemException;
use Magento\RemoteStorage\Driver\Adapter\MetadataProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @see AwsS3
 */
class AwsS3Test extends TestCase
{
    private const URL = 'https://test.s3.amazonaws.com/';

    /**
     * @var AwsS3
     */
    private $driver;

    /**
     * @var FilesystemAdapter|MockObject
     */
    private $adapterMock;

    /**
     * @var MetadataProviderInterface|MockObject
     */
    private $metadataProviderMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->adapterMock = $this->getMockForAbstractClass(FilesystemAdapter::class);
        $this->metadataProviderMock = $this->getMockForAbstractClass(MetadataProviderInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->driver = new AwsS3($this->adapterMock, $loggerMock, self::URL, $this->metadataProviderMock);
    }

    /**
     * @param string|null $basePath
     * @param string|null $path
     * @param string $expected
     *
     * @dataProvider getAbsolutePathDataProvider
     */
    public function testGetAbsolutePath($basePath, $path, string $expected): void
    {
        self::assertSame($expected, $this->driver->getAbsolutePath($basePath, $path));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAbsolutePathDataProvider(): array
    {
        return [
            [
                null,
                'test.png',
                self::URL . 'test.png'
            ],
            [
                self::URL . 'test/test.png',
                null,
                self::URL . 'test/test.png'
            ],
            [
                '',
                'test.png',
                self::URL . 'test.png'
            ],
            [
                '',
                '/test/test.png',
                self::URL . 'test/test.png'
            ],
            [
                self::URL . 'test/test.png',
                self::URL . 'test/test.png',
                self::URL . 'test/test.png'
            ],
            [
                self::URL,
                self::URL . 'media/catalog/test.png',
                self::URL . 'media/catalog/test.png'
            ],
            [
                '',
                self::URL . 'media/catalog/test.png',
                self::URL . 'media/catalog/test.png'
            ],
            [
                self::URL . 'test/',
                'test.txt',
                self::URL . 'test/test.txt'
            ],
            [
                self::URL . 'media/',
                '/catalog/test.png',
                self::URL . 'media/catalog/test.png'
            ],
            [
                self::URL,
                'var/import/images',
                self::URL . 'var/import/images'
            ],
            [
                self::URL . 'export/',
                null,
                self::URL . 'export/'
            ],
            [
                self::URL . 'var/import/images/product_images/',
                self::URL . 'var/import/images/product_images/1.png',
                self::URL . 'var/import/images/product_images/1.png'
            ],
            [
                '',
                self::URL . 'media/catalog/test.png',
                self::URL . 'media/catalog/test.png'
            ],
            [
                self::URL,
                'var/import/images',
                self::URL . 'var/import/images'
            ],
            [
                self::URL . 'var/import/images/product_images/',
                self::URL . 'var/import/images/product_images/1.png',
                self::URL . 'var/import/images/product_images/1.png'
            ],
            [
                self::URL . 'var/import/images/product_images/1.png',
                '',
                self::URL . 'var/import/images/product_images/1.png'
            ],
            [
                self::URL . 'media/',
                '',
                self::URL . 'media/',
            ],
            [
                self::URL . 'media/',
                self::URL . 'media',
                self::URL . 'media',
            ],
            [
                self::URL,
                '',
                self::URL
            ]
        ];
    }

    /**
     * @param string $basePath
     * @param string $path
     * @param string $expected
     *
     * @dataProvider getRelativePathDataProvider
     */
    public function testGetRelativePath(string $basePath, string $path, string $expected): void
    {
        self::assertSame($expected, $this->driver->getRelativePath($basePath, $path));
    }

    /**
     * @return array
     */
    public function getRelativePathDataProvider(): array
    {
        return [
            [
                '',
                'test/test.txt',
                'test/test.txt'
            ],
            [
                '',
                '/test/test.txt',
                '/test/test.txt'
            ],
            [
                self::URL,
                self::URL . 'test/test.txt',
                'test/test.txt'
            ],

        ];
    }

    /**
     * @param string $path
     * @param string $normalizedPath
     * @param bool $has
     * @param array $metadata
     * @param bool $expected
     * @param iterable $listContents
     * @param \Exception|null $metadataException
     * @throws FileSystemException
     * @dataProvider isDirectoryDataProvider
     */
    public function testIsDirectory(
        string $path,
        string $normalizedPath,
        array $metadata,
        bool $expected,
        iterable $listContents,
        \Throwable $listContentsException = null
    ): void {
        if (!empty($metadata)) {
            $this->metadataProviderMock->method('getMetadata')
                ->with($normalizedPath)
                ->willReturn($metadata);
        }
        if ($listContentsException) {
            $this->adapterMock->method('listContents')
                ->with($normalizedPath)
                ->willThrowException($listContentsException);
        } else {
            $this->adapterMock->method('listContents')
                ->with($normalizedPath)
                ->willReturn($listContents);
        }
        self::assertSame($expected, $this->driver->isDirectory($path));
    }

    /**
     * @return array
     */
    public function isDirectoryDataProvider(): array
    {
        return [
            'empty metadata' => [
                'some_directory/',
                'some_directory',
                [],
                false,
                new \ArrayIterator([]),
                new \Exception('Closed iterator'),
            ],
            [
                'some_directory',
                'some_directory',
                [
                    'type' => AwsS3::TYPE_DIR
                ],
                true,
                new \ArrayIterator(['some_directory']),
            ],
            [
                self::URL . 'some_directory',
                'some_directory',
                [
                    'type' => AwsS3::TYPE_DIR
                ],
                true,
                new \ArrayIterator(['some_directory']),
            ],
            [
                '',
                '',
                [
                    'type' => AwsS3::TYPE_DIR
                ],
                true,
                new \ArrayIterator(['']),
            ],
            [
                '/',
                '',
                [
                    'type' => AwsS3::TYPE_DIR
                ],
                true,
                new \ArrayIterator(['']),
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $normalizedPath
     * @param bool $has
     * @param array $metadata
     * @param bool $expected
     * @throws FileSystemException
     *
     * @dataProvider isFileDataProvider
     */
    public function testIsFile(
        string $path,
        string $normalizedPath,
        bool $has,
        array $metadata,
        bool $expected
    ): void {
        $this->adapterMock->method('fileExists')
            ->with($normalizedPath)
            ->willReturn($has);
        $this->metadataProviderMock->method('getMetadata')
            ->with($normalizedPath)
            ->willReturn($metadata);
        self::assertSame($expected, $this->driver->isFile($path));
    }

    /**
     * @return array
     */
    public function isFileDataProvider(): array
    {
        return [
            [
                'some_file.txt',
                'some_file.txt',
                false,
                [],
                false
            ],
            [
                'some_file.txt/',
                'some_file.txt',
                true,
                [
                    'type' => AwsS3::TYPE_FILE
                ],
                true
            ],
            [
                self::URL . 'some_file.txt',
                'some_file.txt',
                true,
                [
                    'type' => AwsS3::TYPE_FILE
                ],
                true
            ],
            [
                self::URL . 'some_file.txt/',
                'some_file.txt',
                true,
                [
                    'type' => AwsS3::TYPE_DIR
                ],
                false
            ],
            [
                '',
                '',
                false,
                [],
                false
            ],
            [
                '/',
                '',
                false,
                [],
                false
            ]
        ];
    }

    /**
     * @param string $path
     * @param string $expected
     *
     * @dataProvider getRealPathSafetyDataProvider
     */
    public function testGetRealPathSafety(string $path, string $expected): void
    {
        self::assertSame($expected, $this->driver->getRealPathSafety($path));
    }

    /**
     * @return array
     */
    public function getRealPathSafetyDataProvider(): array
    {
        return [
            [
                self::URL,
                self::URL
            ],
            [
                'test.txt',
                'test.txt'
            ],
            [
                self::URL . 'test/test/../test.txt',
                self::URL . 'test/test.txt'
            ],
            [
                'test/test/../test.txt',
                'test/test.txt'
            ],
            [
                'test//test/../test.txt',
                'test/test.txt'
            ],
            [
                'test1///test2/..//test3//test.txt',
                'test1/test3/test.txt'
            ],
            [
                self::URL . '/test1///test2/..//test3//test.txt',
                self::URL . 'test1/test3/test.txt'
            ]
        ];
    }

    /**
     * @throws FileSystemException
     */
    public function testSearchDirectory(): void
    {
        $expression = '/*';
        $path = 'path';
        $subPaths = [
            new \League\Flysystem\DirectoryAttributes('path/1/'),
            new \League\Flysystem\DirectoryAttributes('path/2/')
        ];
        $expectedResult = [self::URL . 'path/1/', self::URL . 'path/2/'];
        $this->metadataProviderMock->expects(self::any())->method('getMetadata')
            ->willReturnMap([
                ['path', ['type' => AwsS3::TYPE_DIR]],
                ['path/1', ['type' => AwsS3::TYPE_FILE]],
                ['path/2', ['type' => AwsS3::TYPE_FILE]],
            ]);
        $this->adapterMock->expects(self::atLeastOnce())->method('listContents')
            ->willReturn(new \ArrayIterator($subPaths));

        self::assertEquals($expectedResult, $this->driver->search($expression, $path));
    }

    /**
     * @throws FileSystemException
     */
    public function testSearchFiles(): void
    {
        $expression = '/*';
        $path = 'path';
        $subPaths = [
            new \League\Flysystem\DirectoryAttributes('path/1.jpg'),
            new \League\Flysystem\DirectoryAttributes('path/2.png')
        ];
        $expectedResult = [self::URL . 'path/1.jpg', self::URL . 'path/2.png'];
        $this->metadataProviderMock->expects(self::atLeastOnce())->method('getMetadata')
            ->willReturnMap([
                ['path', ['type' => AwsS3::TYPE_DIR]],
                ['path/1.jpg', ['type' => AwsS3::TYPE_FILE]],
                ['path/2.png', ['type' => AwsS3::TYPE_FILE]],
            ]);
        $this->adapterMock->expects(self::atLeastOnce())->method('listContents')
            ->willReturn($subPaths);

        self::assertEquals($expectedResult, $this->driver->search($expression, $path));
    }

    /**
     * @throws FileSystemException
     */
    public function testCreateDirectory(): void
    {
        $this->metadataProviderMock->expects($this->any())
            ->method('getMetadata')
            ->willReturnCallback(function ($param) {
                if ($param == 'test') {
                    return ['type' => AwsS3::TYPE_DIR];
                } else {
                    throw new UnableToRetrieveMetadata('');
                }
            });
        $this->adapterMock->expects($this->any())
            ->method('listContents')
            ->with('test/test2')
            ->willReturn(new \EmptyIterator());
        $this->adapterMock->expects(self::once())
            ->method('createDirectory')
            ->with('test/test2');

        self::assertTrue($this->driver->createDirectory(self::URL . 'test/test2/'));
    }

    public function testRename(): void
    {
        $this->adapterMock->expects(self::once())
            ->method('move')
            ->with('test/path', 'test/path2');

        self::assertTrue($this->driver->rename('test/path', 'test/path2'));
    }

    public function testRenameSameDestination(): void
    {
        $this->adapterMock->expects(self::never())
            ->method('move');

        self::assertTrue($this->driver->rename('test/path', 'test/path'));
    }

    public function testFileShouldBeRewindBeforeSave(): void
    {
        $resource = $this->driver->fileOpen('test/path', 'w');
        $this->driver->fileWrite($resource, 'abc');
        $this->adapterMock->method('fileExists')->willReturn(false);
        $this->adapterMock->expects($this->once())
            ->method('writeStream')
            ->with(
                'test/path',
                $this->callback(
                    // assert that the file pointer is at the beginning of the file before saving it in aws
                    fn ($stream) => $stream === $resource && is_resource($stream) && ftell($stream) === 0
                )
            );
        $this->driver->fileClose($resource);
    }

    public function testFileCloseShouldReturnFalseIfTheArgumentIsNotAResource(): void
    {
        $this->assertEquals(false, $this->driver->fileClose(''));
        $this->assertEquals(false, $this->driver->fileClose(null));
        $this->assertEquals(false, $this->driver->fileClose(false));
    }

    /**
     * @dataProvider fileOpenModesDataProvider
     */
    public function testFileOppenedMode($mode, $expected): void
    {
        $this->adapterMock->method('fileExists')->willReturn(true);
        if ($mode !== 'w') {
            $this->adapterMock->expects($this->once())->method('read')->willReturn('aaa');
        } else {
            $this->adapterMock->expects($this->never())->method('read');
        }
        $resource = $this->driver->fileOpen('test/path', $mode);
        $this->assertEquals($expected, ftell($resource));
    }

    /**
     * Data provider for testFileOppenedMode
     *
     * @return array[]
     */
    public function fileOpenModesDataProvider(): array
    {
        return [
            [
                "mode" => "a",
                "expected" => 3
            ],
            [
                "mode" => "r",
                "expected" => 0
            ],
            [
                "mode" => "w",
                "expected" => 0
            ]
        ];
    }
}
