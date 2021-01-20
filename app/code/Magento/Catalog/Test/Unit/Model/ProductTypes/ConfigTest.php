<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ProductTypes;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->readerMock = $this->createMock(\Magento\Catalog\Model\ProductTypes\Config\Reader::class);
        $this->cacheMock = $this->createMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
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
            \Magento\Catalog\Model\ProductTypes\Config::class,
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
    public function getTypeDataProvider()
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
            \Magento\Catalog\Model\ProductTypes\Config::class,
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
            \Magento\Catalog\Model\ProductTypes\Config::class,
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
