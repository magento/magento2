<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Cache\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Cache\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_storage = $this->getMock('Magento\Framework\Cache\Config\Data', ['get'], [], '', false);
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
        )->will(
            $this->returnValue(['val1', 'val2'])
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
        )->will(
            $this->returnValue(['someTypeValue'])
        );
        $result = $this->_model->getType('someType');
        $this->assertCount(1, $result);
    }
}
