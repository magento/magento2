<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ProductTypes;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Framework\Json\JsonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config
     */
    protected $config;

    protected function setUp()
    {
        $this->readerMock = $this->getMock(
            \Magento\Catalog\Model\ProductTypes\Config\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        $this->jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);
        \Magento\Catalog\Model\ProductTypes\Config::setJson($this->jsonMock);
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
            ->willReturn(json_encode($value));

        $this->jsonMock->method('decode')
            ->willReturn($value);
        $this->config = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->config->getType('global'));
    }

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
        $this->jsonMock->method('decode')
            ->willReturn(['types' => $expected]);
        $this->config = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->config->getAll());
    }

    public function testIsProductSet()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('');
        $this->jsonMock->method('decode')
            ->willReturn([]);
        $this->config = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');

        $this->assertEquals(false, $this->config->isProductSet('typeId'));
    }
}
