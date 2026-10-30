<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Initial\Reader;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InitialTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Initial
     */
    private $config;

    /**
     * @var Config|MockObject
     */
    private $cacheMock;

    /**
     * @var array
     */
    private $data = [
        'data' => [
            'default' => ['key' => 'default_value'],
            'stores' => ['default' => ['key' => 'store_value']],
            'websites' => ['default' => ['key' => 'website_value']],
        ],
        'metadata' => ['metadata'],
    ];

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->cacheMock = $this->createMock(Config::class);
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->with('initial_config')
            ->willReturn(json_encode($this->data));
        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->method('unserialize')
            ->willReturn($this->data);

        $this->config = $this->objectManager->getObject(
            Initial::class,
            [
                'cache' => $this->cacheMock,
                'serializer' => $serializerMock,
            ]
        );
    }

    /**     * @param array $expected
     */
    #[DataProvider('getDataDataProvider')]
    public function testGetData($scope, $expected)
    {
        $this->assertEquals($expected, $this->config->getData($scope));
    }

    /**
     * @return array
     */
    public static function getDataDataProvider()
    {
        return [
            ['default', ['key' => 'default_value']],
            ['stores|default', ['key' => 'store_value']],
            ['websites|default', ['key' => 'website_value']]
        ];
    }

    public function testGetMetadata()
    {
        $this->assertEquals(['metadata'], $this->config->getMetadata());
    }

    /**
     * Stale or invalid cache payloads must not break Initial; reader is used to rebuild and refresh cache.
     *
     * @return void
     */
    public function testCorruptedCacheEntryTriggersReread(): void
    {
        $goodData = [
            'data' => [
                'default' => ['key' => 'from_reader'],
            ],
            'metadata' => [],
        ];
        $cacheMock = $this->createMock(Config::class);
        $cacheMock->expects($this->once())
            ->method('load')
            ->with(Initial::CACHE_ID)
            ->willReturn('stale-payload');
        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('stale-payload')
            ->willReturn([]);
        $readerMock = $this->createMock(Reader::class);
        $readerMock->expects($this->once())
            ->method('read')
            ->willReturn($goodData);
        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($goodData)
            ->willReturn(json_encode($goodData));
        $cacheMock->expects($this->once())
            ->method('save')
            ->with(json_encode($goodData), Initial::CACHE_ID);

        $config = $this->objectManager->getObject(
            Initial::class,
            [
                'reader' => $readerMock,
                'cache' => $cacheMock,
                'serializer' => $serializerMock,
            ]
        );
        $this->assertEquals(['key' => 'from_reader'], $config->getData('default'));
        $this->assertEquals([], $config->getMetadata());
    }
}
