<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Unit\Driver;

use League\Flysystem\AwsS3v3\AwsS3V3Adapter;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use Magento\AwsS3\Driver\AwsS3;
use Magento\Framework\Exception\FileSystemException;
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
     * @var AwsS3V3Adapter|MockObject
     */
    private $adapterMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->adapterMock = $this->getMockBuilder(FilesystemAdapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMetadata'])
            ->getMockForAbstractClass();
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->driver = new AwsS3($this->adapterMock, $loggerMock, self::URL);
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
     */
    public function getAbsolutePathDataProvider(): array
    {
        return [
            [
                null,
                'test.png',
                self::URL . 'test.png',
            ],
            [
                self::URL . 'test/test.png',
                null,
                self::URL . 'test/test.png',
            ],
            [
                '',
                'test.png',
                self::URL . 'test.png',
            ],
            [
                '',
                '/test/test.png',
                self::URL . 'test/test.png',
            ],
            [
                self::URL . 'test/test.png',
                self::URL . 'test/test.png',
                self::URL . 'test/test.png',
            ],
            [
                self::URL,
                self::URL . 'media/catalog/test.png',
                self::URL . 'media/catalog/test.png',
            ],
            [
                '',
                self::URL . 'media/catalog/test.png',
                self::URL . 'media/catalog/test.png',
            ],
            [
                self::URL . 'test/',
                'test.txt',
                self::URL . 'test/test.txt',
            ],
            [
                self::URL . 'media/',
                '/catalog/test.png',
                self::URL . 'media/catalog/test.png',
            ],
            [
                self::URL,
                'var/import/images',
                self::URL . 'var/import/images',
            ],
            [
                self::URL . 'export/',
                null,
                self::URL . 'export/',
            ],
            [
                self::URL . 'var/import/images/product_images/',
                self::URL . 'var/import/images/product_images/1.png',
                self::URL . 'var/import/images/product_images/1.png',
            ],
            [
                '',
                self::URL . 'media/catalog/test.png',
                self::URL . 'media/catalog/test.png',
            ],
            [
                self::URL,
                'var/import/images',
                self::URL . 'var/import/images',
            ],
            [
                self::URL . 'var/import/images/product_images/',
                self::URL . 'var/import/images/product_images/1.png',
                self::URL . 'var/import/images/product_images/1.png',
            ],
            [
                self::URL . 'var/import/images/product_images/1.png',
                '',
                self::URL . 'var/import/images/product_images/1.png',
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
                self::URL,
            ],
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
                'test/test.txt',
            ],
            [
                '',
                '/test/test.txt',
                '/test/test.txt',
            ],
            [
                self::URL,
                self::URL . 'test/test.txt',
                'test/test.txt',
            ],

        ];
    }

    /**
     * @param string $path
     * @param array $dirs
     * @param bool $expected
     * @throws FileSystemException
     *
     * @dataProvider isDirectoryDataProvider
     */
    public function testIsDirectory(
        string $path,
        array $dirs,
        bool $expected
    ): void {
        $directoryListing = $dirs;
        $this->adapterMock->method('listContents')
            ->willReturn($directoryListing);

        self::assertSame($expected, $this->driver->isDirectory($path));
    }

    /**
     * @return array
     */
    public function isDirectoryDataProvider(): array
    {
        return [
            [
                'some_directory/',
                [],
                false,
            ],
            [
                'some_directory',
                [
                    new DirectoryAttributes('some_directory'),
                ],
                true,
            ],
            [
                self::URL . 'some_directory',
                [
                    new DirectoryAttributes('some_directory'),
                    new DirectoryAttributes('some_directory_1'),
                ],
                true,
            ],
            [
                self::URL . 'some_directory',
                [
                    new DirectoryAttributes('some_directory_1'),
                ],
                false,
            ],
            [
                '',
                [],
                true,
            ],
            [
                '/',
                [],
                true,
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
        bool $expected
    ): void {
        $this->adapterMock->method('fileExists')
            ->with($normalizedPath)
            ->willReturn($has);

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
                false,
            ],
            [
                'some_file.txt/',
                'some_file.txt',
                true,
                true,
            ],
            [
                self::URL . 'some_file.txt',
                'some_file.txt',
                true,
                true,
            ],
            [
                '',
                '',
                false,
                false,
            ],
            [
                '/',
                '',
                false,
                false,
            ],
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
                self::URL,
            ],
            [
                'test.txt',
                'test.txt',
            ],
            [
                self::URL . 'test/test/../test.txt',
                self::URL . 'test/test.txt',
            ],
            [
                'test/test/../test.txt',
                'test/test.txt',
            ],
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
            new DirectoryAttributes('path/1'),
            new DirectoryAttributes('path/2'),
        ];

        $expectedResult = [self::URL . 'path/1/', self::URL . 'path/2/'];
        $directoryListing = [new DirectoryAttributes('path')];
        $this->adapterMock->expects(self::exactly(4))->method('listContents')
            ->willReturnOnConsecutiveCalls(
                $directoryListing,
                $subPaths,
                $subPaths,
                $subPaths
            );

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
            new FileAttributes('path/1.jpg'),
            new FileAttributes('path/2.png'),
        ];
        $expectedResult = [self::URL . 'path/1.jpg', self::URL . 'path/2.png'];
        $directoryListing = [new DirectoryAttributes('path')];
        $this->adapterMock->expects(self::exactly(4))->method('listContents')
            ->willReturnOnConsecutiveCalls(
                $directoryListing,
                $subPaths,
                $subPaths,
                $subPaths
            );

        self::assertEquals($expectedResult, $this->driver->search($expression, $path));
    }

    /**
     * @throws FileSystemException
     */
    public function testCreateDirectory(): void
    {
        $this->adapterMock->expects(self::once())
            ->method('createDirectory')
            ->with('test/test2');
        $directoryListing = [new DirectoryAttributes('test')];
        $this->adapterMock->expects(self::exactly(2))->method('listContents')
            ->willReturnOnConsecutiveCalls(
                $directoryListing,
                [],
            );

        self::assertTrue($this->driver->createDirectory(self::URL . 'test/test2/'));
    }
}
