<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Quote
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_configMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_adapterMock;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $_selectMock;

    protected function setUp()
    {
        $this->_selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $this->_selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->_selectMock->expects($this->any())->method('where');

        $this->_adapterMock = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $this->_adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->_selectMock));

        $this->_resourceMock = $this->getMock('\Magento\Framework\App\Resource', [], [], '', false);
        $this->_resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_adapterMock)
        );

        $this->_configMock = $this->getMock('\Magento\Eav\Model\Config', [], [], '', false);

        $this->_model = new \Magento\Sales\Model\Resource\Quote(
            $this->_resourceMock,
            $this->_configMock
        );
    }

    /**
     * @param $value
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsOrderIncrementIdUsed($value)
    {
        $expectedBind = [':increment_id' => $value];
        $this->_adapterMock->expects($this->once())->method('fetchOne')->with($this->_selectMock, $expectedBind);
        $this->_model->isOrderIncrementIdUsed($value);
    }

    /**
     * @return array
     */
    public function isOrderIncrementIdUsedDataProvider()
    {
        return [[100000001], ['10000000001'], ['M10000000001']];
    }
}
