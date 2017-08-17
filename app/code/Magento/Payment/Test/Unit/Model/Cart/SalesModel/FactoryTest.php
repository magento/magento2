<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Cart\SalesModel;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Model\Cart\SalesModel\Factory */
    protected $_model;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Payment\Model\Cart\SalesModel\Factory($this->_objectManagerMock);
    }

    /**
     * @param string $salesModelClass
     * @param string $expectedType
     * @dataProvider createDataProvider
     */
    public function testCreate($salesModelClass, $expectedType)
    {
        $salesModel = $this->createPartialMock($salesModelClass, ['__wakeup']);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedType,
            ['salesModel' => $salesModel]
        )->will(
            $this->returnValue('some value')
        );
        $this->assertEquals('some value', $this->_model->create($salesModel));
    }

    public function createDataProvider()
    {
        return [
            [\Magento\Quote\Model\Quote::class, \Magento\Payment\Model\Cart\SalesModel\Quote::class],
            [\Magento\Sales\Model\Order::class, \Magento\Payment\Model\Cart\SalesModel\Order::class]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalid()
    {
        $this->_model->create('any invalid');
    }
}
