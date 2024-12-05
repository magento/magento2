<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Test\Unit\Driver\File;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File\Mime;
use PHPUnit\Framework\TestCase;

/**
 * @see Mime
 */
class MimeTest extends TestCase
{
    /**
     * @var Mime
     */
    private $mime;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->mime = new Mime();
    }

    public function testGetMimeTypeNonexistentFileException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage('File \'nonexistent.file\' doesn\'t exist');

        $file = 'nonexistent.file';
        $this->mime->getMimeType($file);
    }

    /**
     * @param string $file
     * @param string $expectedType
     * @throws FileSystemException
     *
     * @dataProvider getMimeTypeDataProvider
     */
    public function testGetMimeType(string $file, string $expectedType): void
    {
        $actualType = $this->mime->getMimeType($file);
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
}
