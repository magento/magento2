<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Model\ResourceModel\History;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HistoryTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var History|MockObject
     */
    protected $historyResourceModel;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->historyResourceModel = $this->createPartialMock(
            History::class,
            ['getConnection', 'getMainTable', 'getIdFieldName']
        );
        $dbAdapterMock = $this->createPartialMock(
            Mysql::class,
            ['select', 'fetchOne']
        );
        $selectMock = $this->createPartialMock(
            Select::class,
            ['from', 'order', 'where', 'limit']
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
