<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Cart\SalesModel;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Cart\SalesModel\Factory */
    protected $_model;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new \Magento\Payment\Model\Cart\SalesModel\Factory($this->_objectManagerMock);
    }

    /**
     * @param string $salesModelClass
     * @param string $expectedType
     * @dataProvider createDataProvider
     */
    public function testCreate($salesModelClass, $expectedType)
    {
        $salesModel = $this->getMock($salesModelClass, ['__wakeup'], [], '', false);
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
            ['Magento\Sales\Model\Quote', 'Magento\Payment\Model\Cart\SalesModel\Quote'],
            ['Magento\Sales\Model\Order', 'Magento\Payment\Model\Cart\SalesModel\Order']
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
