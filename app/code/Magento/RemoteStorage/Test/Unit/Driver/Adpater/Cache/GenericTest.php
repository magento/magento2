<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RemoteStorage\Test\Unit\Driver\Adpater\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\RemoteStorage\Driver\Adapter\Cache\Generic;
use Magento\RemoteStorage\Driver\Adapter\PathUtil;
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

    protected function setUp(): void
    {
        $this->cacheAdapterMock = $this->createMock(CacheInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->pathUtilMock = $this->createMock(PathUtil::class);

        $this->generic = new Generic(
            $this->cacheAdapterMock,
            $this->serializerMock,
            $this->pathUtilMock
        );
    }

    /**
     * @param string $input
     * @param array|null $expectedOutput
     * @dataProvider metaDataProvider
     */
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
    public function metaDataProvider(): array
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

    protected function tearDown(): void
    {
        unset($this->generic);
        unset($this->cacheAdapterMock);
        unset($this->serializerMock);
        unset($this->pathUtilMock);
    }
}
