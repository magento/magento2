<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Model\ResourceModel\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxTest extends TestCase
{
    /**
     * @var Tax
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $connectionMock;

    /**
     * @var MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->selectMock = $this->createMock(Select::class);

        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('table_name');

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceMock);

        $this->model = $objectManager->getObject(
            Tax::class,
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
