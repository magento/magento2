<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test mime type utility for correct.
 *
 * @deprecated
 */
class MimeTest extends TestCase
{
    /**
     * @var Mime
     */
    private $object;

    /**
     * @var Filesystem\DriverInterface|MockObject
     */
    private $localDriverMock;

    /**
     * @var Filesystem\DriverInterface|MockObject
     */
    private $remoteDriverMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var Filesystem\Directory\WriteInterface|MockObject
     */
    private $localDirectoryMock;

    /**
     * @var Filesystem\Directory\WriteInterface|MockObject
     */
    private $remoteDirectoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->localDriverMock = $this->getMockForAbstractClass(Filesystem\DriverInterface::class);
        $this->remoteDriverMock = $this->getMockForAbstractClass(Filesystem\ExtendedDriverInterface::class);

        $this->localDirectoryMock = $this->getMockForAbstractClass(Filesystem\Directory\WriteInterface::class);
        $this->localDirectoryMock->method('getDriver')
            ->willReturn($this->localDriverMock);
        $this->remoteDirectoryMock = $this->getMockForAbstractClass(Filesystem\Directory\WriteInterface::class);
        $this->remoteDirectoryMock->method('getDriver')
            ->willReturn($this->remoteDriverMock);

        /** @var Filesystem|MockObject $filesystem */
        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->object = new Mime($this->filesystemMock);
    }

    public function testGetMimeTypeNonexistentFileException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage('File \'nonexistent.file\' doesn\'t exist');

        $this->filesystemMock->method('getDirectoryWrite')->willReturn(
            $this->localDirectoryMock
        );
        $this->localDriverMock->expects(self::exactly(2))
            ->method('isExists')
            ->with('nonexistent.file')
            ->willReturn(true);

        $file = 'nonexistent.file';
        $this->object->getMimeType($file);
    }

    /**
     * @param string $file
     * @param string $expectedType
     *
     * @dataProvider getMimeTypeDataProvider
     */
    public function testGetMimeType($file, $expectedType): void
    {
        $this->filesystemMock->method('getDirectoryWrite')->willReturn(
            $this->localDirectoryMock
        );
        $this->localDriverMock->expects(self::exactly(2))
            ->method('isExists')
            ->with($file)
            ->willReturn(true);

        $actualType = $this->object->getMimeType($file);
        self::assertSame($expectedType, $actualType);
    }

    /**
     * @return array
     */
    public static function getMimeTypeDataProvider(): array
    {
        return [
            'javascript' => [__DIR__ . '/_files/javascript.js', 'application/javascript'],
            'weird extension' => [__DIR__ . '/_files/file.weird', 'application/octet-stream'],
            'weird uppercase extension' => [__DIR__ . '/_files/UPPERCASE.WEIRD', 'application/octet-stream'],
            'generic mime type' => [__DIR__ . '/_files/blank.html', 'text/html'],
            'tmp file mime type' => [__DIR__ . '/_files/magento', 'image/jpeg'],
        ];
    }

    /**
     * @param string $file
     * @param string $expectedType
     *
     * @dataProvider getMimeTypeDataProvider
     */
    public function testGetMimeTypeRemote($file, $expectedType): void
    {
        $this->filesystemMock->method('getDirectoryWrite')->willReturnOnConsecutiveCalls(
            $this->localDirectoryMock,
            $this->remoteDirectoryMock
        );
        $this->localDriverMock->method('isExists')
            ->willReturn(false);
        $this->remoteDriverMock->expects(self::once())
            ->method('isExists')
            ->with($file)
            ->willReturn(true);
        $this->remoteDriverMock->method('getMetadata')
            ->willReturn(['mimetype' => $expectedType]);

        $actualType = $this->object->getMimeType($file);
        self::assertSame($expectedType, $actualType);
    }
}
