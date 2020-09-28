<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\File\Mime;
use PHPUnit\Framework\TestCase;

/**
 * Test mime type utility for correct
 */
class MimeTest extends TestCase
{
    /**
     * @var Mime
     */
    private $object;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->object = new Mime();
    }

    public function testGetMimeTypeNonexistentFileException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('File \'nonexistent.file\' doesn\'t exist');
        $file = 'nonexistent.file';
        $this->object->getMimeType($file);
    }

    /**
     * @param string $file
     * @param string $expectedType
     *
     * @dataProvider getMimeTypeDataProvider
     */
    public function testGetMimeType($file, $expectedType)
    {
        $actualType = $this->object->getMimeType($file);
        $this->assertSame($expectedType, $actualType);
    }

    /**
     * @return array
     */
    public function getMimeTypeDataProvider(): array
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
