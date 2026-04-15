<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

namespace Magento\RemoteStorage\Test\Unit\Driver\Adpater\Cache;

use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface as AdapterCacheInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\RemoteStorage\Driver\Adapter\Cache\Generic;
use Magento\RemoteStorage\Driver\Adapter\PathUtil;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see Generic
 */
class GenericTest extends TestCase
{
    /**
     * @var Generic
     */
    private Generic $generic;

    /**
     * @var CacheInterface|MockObject
     */
    private CacheInterface $cacheAdapterMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private SerializerInterface $serializerMock;

    /**
     * @var PathUtil|MockObject
     */
    private PathUtil $pathUtilMock;

    /**
     * @var int
     */
    private int $ttl;

    protected function setUp(): void
    {
        $this->cacheAdapterMock = $this->createMock(CacheInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->pathUtilMock = $this->createMock(PathUtil::class);
        $this->ttl = 300;

        $this->generic = new Generic(
            $this->cacheAdapterMock,
            $this->serializerMock,
            $this->pathUtilMock,
            'flysystem:',
            $this->ttl
        );
    }

    /**
     * @return void
     */
    public function testUpdateMetadataPersists(): void
    {
        $path           = 'dir/file.txt';
        $objectMetadata = ['type' => 'file', 'size' => 123];

        $this->pathUtilMock
            ->expects($this->exactly(2))
            ->method('pathInfo')
            ->willReturnOnConsecutiveCalls(
                ['dirname' => 'dir', 'path' => $path],
                ['dirname' => '', 'path' => 'dir']
            );

        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->with($this->callback(function (array $data) use ($path) {
                return isset($data[$path])
                    && $data[$path]['type'] === 'file'
                    && $data[$path]['size'] === 123
                    && $data[$path]['dirname'] === 'dir'
                    && $data[$path]['path'] === $path;
            }))
            ->willReturn('SERIALIZED_DATA');

        $this->cacheAdapterMock
            ->expects($this->once())
            ->method('save')
            ->with(
                'SERIALIZED_DATA',
                'flysystem:' . $path,
                [AdapterCacheInterface::CACHE_TAG],
                $this->ttl
            );

        $this->cacheAdapterMock
            ->expects($this->never())
            ->method('load');

        $this->generic->updateMetadata($path, $objectMetadata, true);
    }

    /**
     * @return void
     */
    public function testStoreFileNotExistsMarksPathAsNonExistingAndPersists(): void
    {
        $path = 'missing/file.txt';

        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->with([$path => false])
            ->willReturn('SERIALIZED_MISSING');

        $this->cacheAdapterMock
            ->expects($this->once())
            ->method('save')
            ->with(
                'SERIALIZED_MISSING',
                'flysystem:' . $path,
                [AdapterCacheInterface::CACHE_TAG],
                $this->ttl
            );

        $this->generic->storeFileNotExists($path);

        $this->cacheAdapterMock
            ->expects($this->never())
            ->method('load');

        $this->assertFalse($this->generic->exists($path));
    }

    /**
     * @return void
     */
    public function testMoveFileMovesMetadataAndPurgesOldKey(): void
    {
        $oldPath = 'old/file.txt';
        $newPath = 'new/file.txt';

        $this->cacheAdapterMock
            ->expects($this->exactly(2))
            ->method('load')
            ->willReturnOnConsecutiveCalls(
                'SERIALIZED_OLD',
                false
            );

        $oldMeta = [
            $oldPath => [
                'path'    => $oldPath,
                'dirname' => 'old',
                'type'    => 'file',
            ],
        ];

        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with('SERIALIZED_OLD')
            ->willReturn($oldMeta);

        $this->pathUtilMock
            ->expects($this->once())
            ->method('pathInfo')
            ->with($newPath)
            ->willReturn(['dirname' => 'new', 'path' => $newPath]);

        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->with($this->callback(function (array $data) use ($newPath) {
                if (!isset($data[$newPath])) {
                    return false;
                }
                $meta = $data[$newPath];

                return $meta['path'] === $newPath
                    && $meta['dirname'] === 'new'
                    && $meta['type'] === 'file';
            }))
            ->willReturn('SERIALIZED_NEW');

        $this->cacheAdapterMock
            ->expects($this->once())
            ->method('save')
            ->with(
                'SERIALIZED_NEW',
                'flysystem:' . $newPath,
                [AdapterCacheInterface::CACHE_TAG],
                $this->ttl
            );

        $this->cacheAdapterMock
            ->expects($this->once())
            ->method('remove')
            ->with('flysystem:' . $oldPath);

        $this->generic->moveFile($oldPath, $newPath);

        $this->assertTrue($this->generic->exists($newPath));
        $this->assertNull($this->generic->exists($oldPath));
    }

    /**
     * @param string $input
     * @param array|null $expectedOutput
     */
    #[DataProvider('metaDataProvider')]
    public function testGetMetaData(string $input, ?array $expectedOutput): void
    {
        $cacheData = include __DIR__ . '/_files/CacheData.php';
        $this->cacheAdapterMock
            ->expects($this->once())
            ->method('load')
            ->willReturn(json_encode($cacheData));
        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->willReturn($cacheData);

        $this->assertEquals($expectedOutput, $this->generic->getMetaData($input));
    }

    /**
     * @return array
     */
    public static function metaDataProvider(): array
    {
        return [
            [
                'media',
                [
                    'path' => 'media',
                    'dirname' => '.',
                    'basename' => 'media',
                    'filename' => 'media',
                    'type' => 'dir',
                    'size' => null,
                    'timestamp' => null,
                    'visibility' => null,
                    'mimetype' => '',
                ],
            ],
            [
                'media/tmp/catalog/product/1/test.jpeg',
                [
                    'path' => 'media/tmp/catalog/product/1/test.jpeg',
                    'dirname' => 'media/tmp/catalog/product/1',
                    'basename' => 'test.jpeg',
                    'extension' => 'jpeg',
                    'filename' => 'test.jpeg',
                    'type' => 'file',
                    'size' => '87066',
                    'timestamp' => '1635860865',
                    'visibility' => null,
                    'mimetype' => 'image/jpeg',
                    'extra' => [
                        'image-width' => 680,
                        'image-height' => 383,
                    ],
                ],
            ],
            [
                'media-nonexistent-path',
                null,
            ],
        ];
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->generic);
        unset($this->cacheAdapterMock);
        unset($this->serializerMock);
        unset($this->pathUtilMock);
    }
}
