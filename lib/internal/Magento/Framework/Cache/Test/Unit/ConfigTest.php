<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Cache\Config\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storage;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Cache\Config
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_storage = $this->createPartialMock(\Magento\Framework\Cache\Config\Data::class, ['get']);
        $this->_model = new \Magento\Framework\Cache\Config($this->_storage);
    }

    public function testGetTypes()
    {
        $this->_storage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'types',
            []
        )->willReturn(
            ['val1', 'val2']
        );
        $result = $this->_model->getTypes();
        $this->assertCount(2, $result);
    }

    public function testGetType()
    {
        $this->_storage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'types/someType',
            []
        )->willReturn(
            ['someTypeValue']
        );
        $result = $this->_model->getType('someType');
        $this->assertCount(1, $result);
    }
}
