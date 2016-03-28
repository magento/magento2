<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class HistoryTest
 */
class HistoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyResourceModel;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->historyResourceModel = $this->getMock(
            'Magento\ImportExport\Model\ResourceModel\History',
            ['getConnection', 'getMainTable', 'getIdFieldName'],
            [],
            '',
            false
        );
        $dbAdapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'fetchOne'],
            [],
            '',
            false
        );
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['from', 'order', 'where', 'limit'],
            [],
            '',
            false
        );
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('order')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('where')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('limit')->will($this->returnSelf());
        $dbAdapterMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $dbAdapterMock->expects($this->any())->method('fetchOne')->will($this->returnValue('result'));
        $this->historyResourceModel->expects($this->any())->method('getConnection')->willReturn($dbAdapterMock);
        $this->historyResourceModel->expects($this->any())->method('getMainTable')->willReturn('mainTable');
        $this->historyResourceModel->expects($this->any())->method('getIdFieldName')->willReturn('id');
    }

    /**
     * Test getLastInsertedId()
     */
    public function testGetLastInsertedId()
    {
        $id = 1;
        $this->assertEquals($this->historyResourceModel->getLastInsertedId($id), 'result');
    }
}
