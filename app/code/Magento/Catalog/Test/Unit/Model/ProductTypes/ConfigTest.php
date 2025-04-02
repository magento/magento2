<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductTypes;

use Magento\Catalog\Model\ProductTypes\Config;
use Magento\Catalog\Model\ProductTypes\Config\Reader;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Reader|MockObject
     */
    private $readerMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->readerMock = $this->createMock(Reader::class);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
    }

    /**
     * @param array $value
     * @param mixed $expected
     * @dataProvider getTypeDataProvider
     */
    public function testGetType($value, $expected)
    {
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->willReturn('serializedData');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($value);

        $this->config = $this->objectManager->getObject(
            Config::class,
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
                'serializer' => $this->serializerMock,
            ]
        );
        $this->assertEquals($expected, $this->config->getType('global'));
    }

    /**
     * @return array
     */
    public static function getTypeDataProvider()
    {
        return [
            'global_key_exist' => [['types' => ['global' => 'value']], 'value'],
            'return_default_value' => [['types' => ['some_key' => 'value']], []]
        ];
    }

    public function testGetAll()
    {
        $expected = ['Expected Data'];
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(json_encode('"types":["Expected Data"]]'));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn(['types' => $expected]);

        $this->config = $this->objectManager->getObject(
            Config::class,
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
                'serializer' => $this->serializerMock,
            ]
        );
        $this->assertEquals($expected, $this->config->getAll());
    }

    public function testIsProductSet()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn([]);

        $this->config = $this->objectManager->getObject(
            Config::class,
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
                'serializer' => $this->serializerMock,
            ]
        );
        $this->assertFalse($this->config->isProductSet('typeId'));
    }
}
