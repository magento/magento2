<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Catalog\Model\ProductTypes\Config
     */
    protected $model;

    protected function setUp()
    {
        $this->readerMock = $this->getMock(
            'Magento\Catalog\Model\ProductTypes\Config\Reader',
            [],
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');
    }

    /**
     * @dataProvider getTypeDataProvider
     *
     * @param array $value
     * @param mixed $expected
     */
    public function testGetType($value, $expected)
    {
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue(serialize($value)));
        $this->model = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->model->getType('global'));
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
        $this->cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->returnValue(serialize(['types' => $expected]))
        );
        $this->model = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->model->getAll());
    }

    public function testIsProductSet()
    {
        $this->cacheMock->expects($this->once())->method('load')->will($this->returnValue(serialize([])));
        $this->model = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');

        $this->assertEquals(false, $this->model->isProductSet('typeId'));
    }
}
