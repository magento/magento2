<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Resource;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Resource\Quote
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resourceMock;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $this->adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->getMock('\Magento\Framework\App\Resource', [], [], '', false);
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->adapterMock)
        );

        $this->configMock = $this->getMock('\Magento\Eav\Model\Config', [], [], '', false);

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->model = new \Magento\Quote\Model\Resource\Quote(
            $contextMock,
            $this->configMock
        );
    }

    /**
     * @param $value
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsOrderIncrementIdUsed($value)
    {
        $expectedBind = [':increment_id' => $value];
        $this->adapterMock->expects($this->once())->method('fetchOne')->with($this->selectMock, $expectedBind);
        $this->model->isOrderIncrementIdUsed($value);
    }

    /**
     * @return array
     */
    public function isOrderIncrementIdUsedDataProvider()
    {
        return [[100000001], ['10000000001'], ['M10000000001']];
    }
}
