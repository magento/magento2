<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel;

use Magento\Reports\Model\ResourceModel\Helper;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->getMock();

        $this->resourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->helper = new Helper(
            $this->resourceMock
        );
    }

    /**
     * @return void
     */
    public function testMergeVisitorProductIndex()
    {
        $mainTable = 'mainTable';
        $data = ['dataKey' => 'dataValue'];
        $matchFields = ['matchField'];

        $this->connectionMock
            ->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($mainTable, $data, array_keys($data));

        $this->helper->mergeVisitorProductIndex($mainTable, $data, $matchFields);
    }

    /**
     * @param string $type
     * @param array $result
     * @dataProvider typesDataProvider
     * @return void
     */
    public function testUpdateReportRatingPos($type, $result)
    {
        $mainTable = 'mainTable';
        $column = 'column';
        $aggregationTable = 'aggregationTable';

        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock
            ->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('group')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('order')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('insertFromSelect')
            ->with($aggregationTable, $result)
            ->willReturnSelf();

        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $this->helper->updateReportRatingPos($this->connectionMock, $type, $column, $mainTable, $aggregationTable);
    }

    /**
     * @return array
     */
    public function typesDataProvider()
    {
        $mResult = ['period', 'store_id', 'product_id', 'product_name', 'product_price', 'column', 'rating_pos'];
        $dResult = ['period', 'store_id', 'product_id', 'product_name', 'product_price', 'id', 'column', 'rating_pos'];
        return [
            ['type' => 'year', 'result' => $mResult],
            ['type' => 'month', 'result' => $mResult],
            ['type' => 'day', 'result' => $dResult],
            ['type' => null, 'result' => $mResult]
        ];
    }
}
