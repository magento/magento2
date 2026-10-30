<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductRepository;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductRepository\ImageContentHashProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImageContentHashProviderTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $mediaConfigMock;

    /**
     * @var ReadInterface|MockObject
     */
    private $mediaDirectoryMock;

    /**
     * @var ImageContentHashProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->mediaConfigMock = $this->createMock(Config::class);
        $this->mediaDirectoryMock = $this->createMock(ReadInterface::class);
        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->provider = new ImageContentHashProvider($this->mediaConfigMock, $filesystemMock);
    }

    /**
     * Existing images sharing identical content must be grouped under one hash as a queue of
     * value_ids, so they can later be matched one-to-one instead of collapsing onto a single id.
     *
     * @return void
     */
    public function testIdenticalContentIsGroupedAsQueue(): void
    {
        $existingMediaGallery = [
            ['value_id' => 1, 'file' => '/t/e/image1.jpg'],
            ['value_id' => 2, 'file' => '/t/e/image2.jpg'],
            ['value_id' => 3, 'file' => '/t/e/other.jpg'],
        ];

        $this->mediaConfigMock->method('getMediaPath')
            ->willReturnCallback(static fn (string $file): string => 'catalog/product' . $file);
        $this->mediaDirectoryMock->method('isExist')->willReturn(true);
        $this->mediaDirectoryMock->method('readFile')
            ->willReturnCallback(
                static fn (string $path): string => $path === 'catalog/product/t/e/other.jpg'
                    ? 'other-bytes'
                    : 'same-bytes'
            );

        $map = $this->provider->getExistingValueIdsByContentHash($existingMediaGallery);

        $this->assertSame([sha1('same-bytes'), sha1('other-bytes')], array_keys($map));
        $this->assertSame([1, 2], $map[sha1('same-bytes')]);
        $this->assertSame([3], $map[sha1('other-bytes')]);
    }

    /**
     * Entries missing a file or value_id, or whose file is not on disk, must be skipped.
     *
     * @return void
     */
    public function testEntriesWithoutResolvableFileAreSkipped(): void
    {
        $existingMediaGallery = [
            ['value_id' => 1], // no file
            ['file' => '/t/e/no-id.jpg'], // no value_id
            ['value_id' => 2, 'file' => '/t/e/missing.jpg'], // not on disk
        ];

        $this->mediaConfigMock->method('getMediaPath')->willReturn('catalog/product/t/e/missing.jpg');
        $this->mediaDirectoryMock->method('isExist')->willReturn(false);

        $this->assertSame([], $this->provider->getExistingValueIdsByContentHash($existingMediaGallery));
    }
}
