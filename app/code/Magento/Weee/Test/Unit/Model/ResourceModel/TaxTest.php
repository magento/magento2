<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model\ResourceModel;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Model\ResourceModel\Tax
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');

        $this->selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);

        $this->connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->getMock('\Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);


        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('table_name');

        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceMock);

        $this->model = $this->objectManager->getObject(
            'Magento\Weee\Model\ResourceModel\Tax',
            [
                'context' => $contextMock,
            ]
        );
    }

    public function testInWeeeLocation()
    {
        $this->selectMock->expects($this->at(1))
            ->method('where')
            ->with('website_id IN(?)', [1, 0])
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->at(2))
            ->method('where')
            ->with('country = ?', 'US')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->at(3))
            ->method('where')
            ->with('state = ?', 0)
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->any())
            ->method('from')
            ->with('table_name', 'value')
            ->willReturn($this->selectMock);

        $this->model->isWeeeInLocation('US', 0, 1);
    }

    public function testFetchWeeeTaxCalculationsByEntity()
    {
        $this->selectMock->expects($this->any())
            ->method('where')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->any())
            ->method('from')
            ->with(
                ['eavTable' => 'table_name'],
                [
                    'eavTable.attribute_code',
                    'eavTable.attribute_id',
                    'eavTable.frontend_label'
                ]
            )->willReturn($this->selectMock);

        $this->selectMock->expects($this->any())
            ->method('joinLeft')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->any())
            ->method('joinInner')
            ->willReturn($this->selectMock);

        $this->model->fetchWeeeTaxCalculationsByEntity('US', 0, 1, 3, 4);
    }
}
