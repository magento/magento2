<?php
/**
 * \Magento\Framework\DataObject\Copy\Config
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Test\Unit\Copy;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy\Config\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storageMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DataObject\Copy\Config
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_storageMock = $this->createPartialMock(\Magento\Framework\DataObject\Copy\Config\Data::class, ['get']);

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
        $this->_storageMock->expects($this->once())->method('get')->willReturn($expected);
        $result = $this->_model->getFieldsets('global');
        $this->assertEquals($expected, $result);
    }

    public function testGetFieldset()
    {
        $expectedFieldset = ['aspect' => 'firstAspect'];
        $fieldsets = ['test' => $expectedFieldset, 'test_second' => ['aspect' => 'secondAspect']];
        $this->_storageMock->expects($this->once())->method('get')->willReturn($fieldsets);
        $result = $this->_model->getFieldset('test');
        $this->assertEquals($expectedFieldset, $result);
    }

    public function testGetFieldsetIfFieldsetIsEmpty()
    {
        $this->_storageMock->expects($this->once())->method('get')
            ->willReturn([]);
        $result = $this->_model->getFieldset('test');
        $this->assertNull($result);
    }
}
