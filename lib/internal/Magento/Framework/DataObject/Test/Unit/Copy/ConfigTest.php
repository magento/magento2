<?php
/**
 * \Magento\Framework\DataObject\Copy\Config
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Test\Unit\Copy;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy\Config\Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject\Copy\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_storageMock = $this->getMock(
            'Magento\Framework\DataObject\Copy\Config\Data',
            ['get'],
            [],
            '',
            false
        );

        $this->_model = new \Magento\Framework\DataObject\Copy\Config($this->_storageMock);
    }

    public function testGetFieldsets()
    {
        $expected = [
            'sales_convert_quote_address' => [
                'company' => ['to_order_address' => '*', 'to_customer_address' => '*'],
                'street_full' => ['to_order_address' => 'street'],
                'street' => ['to_customer_address' => '*'],
            ],
        ];
        $this->_storageMock->expects($this->once())->method('get')->will($this->returnValue($expected));
        $result = $this->_model->getFieldsets('global');
        $this->assertEquals($expected, $result);
    }

    public function testGetFieldset()
    {
        $expectedFieldset = ['aspect' => 'firstAspect'];
        $fieldsets = ['test' => $expectedFieldset, 'test_second' => ['aspect' => 'secondAspect']];
        $this->_storageMock->expects($this->once())->method('get')->will($this->returnValue($fieldsets));
        $result = $this->_model->getFieldset('test');
        $this->assertEquals($expectedFieldset, $result);
    }

    public function testGetFieldsetIfFieldsetIsEmpty()
    {
        $this->_storageMock->expects($this->once())->method('get')
            ->will($this->returnValue([]));
        $result = $this->_model->getFieldset('test');
        $this->assertEquals(null, $result);
    }
}
