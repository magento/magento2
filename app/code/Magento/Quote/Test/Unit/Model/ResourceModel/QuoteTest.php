<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    private $adapterMock;

    /**
     * @var \Magento\Framework\DB\Select
     */
    private $selectMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->adapterMock)
        );

        $this->model = $objectManagerHelper->getObject(
            \Magento\Quote\Model\ResourceModel\Quote::class,
            [
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * Unit test to verify if isOrderIncrementIdUsed method works with different types increment ids
     *
     * @param array $value
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
