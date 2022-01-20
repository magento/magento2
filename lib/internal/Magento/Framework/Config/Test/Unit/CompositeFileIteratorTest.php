<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\Config\CompositeFileIterator;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Filesystem\File\Read;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test composition of file iterators.
 */
class CompositeFileIteratorTest extends TestCase
{
    /**
     * @var ReadFactory|MockObject
     */
    private $readFactoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->readFactoryMock = $this->createMock(ReadFactory::class);
        $this->readFactoryMock->method('create')
            ->willReturnCallback(
                function (string $file): Read {
                    $readMock = $this->createMock(Read::class);
                    $readMock->method('readAll')->willReturn('Content of ' .$file);

                    return $readMock;
                }
            );
    }

    /**
     * Test the composite.
     */
    public function testComposition(): void
    {
        $existingFiles = [
            '/etc/magento/somefile.ext',
            '/etc/magento/somefile2.ext',
            '/etc/magento/somefile3.ext'
        ];
        $newFiles = [
            '/etc/magento/some-other-file.ext',
            '/etc/magento/some-other-file2.ext'
        ];

        $existing = new FileIterator($this->readFactoryMock, $existingFiles);
        $composite = new CompositeFileIterator($this->readFactoryMock, $newFiles, $existing);
        $found = [];
        foreach ($composite as $file => $content) {
            $this->assertNotEmpty($content);
            $found[] = $file;
        }
        $this->assertEquals(array_merge($existingFiles, $newFiles), $found);
        $this->assertEquals(count($existingFiles) + count($newFiles), $composite->count());
        $this->assertEquals(array_merge($existingFiles, $newFiles), array_keys($composite->toArray()));
    }
}
