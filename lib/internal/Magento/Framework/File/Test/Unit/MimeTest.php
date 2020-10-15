<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var ReadInterface|MockObject
     */
    private $readInterface;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->readInterface = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects(self::any())->method('getDirectoryRead')->with(DirectoryList::ROOT)
            ->willReturn($this->readInterface);
        $this->object = new Mime($filesystem);
    }

    public function testGetMimeTypeNonexistentFileException(): void
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
    public function testGetMimeType($file, $expectedType): void
    {
        $actualType = $this->object->getMimeType($file);
        self::assertSame($expectedType, $actualType);
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
