<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\GridAsyncInsert;
use Magento\Sales\Model\ResourceModel\GridInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridAsyncInsertTest extends TestCase
{
    /**
     * @var GridAsyncInsert
     */
    protected $unit;

    /**
     * @var GridInterface|MockObject
     */
    protected $gridAggregatorMock;

    /**
     * @var AbstractModel|MockObject
     */
    protected $salesModelMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigurationMock;

    protected function setUp(): void
    {
        $this->gridAggregatorMock = $this->getMockBuilder(GridInterface::class)
            ->getMockForAbstractClass();
        $this->salesModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getId'
                ]
            )
            ->getMockForAbstractClass();
        $this->scopeConfigurationMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->unit = new GridAsyncInsert(
            $this->gridAggregatorMock,
            $this->scopeConfigurationMock
        );
    }

    public function testAsyncInsert()
    {
        $this->scopeConfigurationMock->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(true);
        $this->gridAggregatorMock->expects($this->once())
            ->method('refreshBySchedule');
        $this->unit->asyncInsert();
    }

    public function testAsyncInsertDisabled()
    {
        $this->scopeConfigurationMock->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(false);
        $this->gridAggregatorMock->expects($this->never())
            ->method('refreshBySchedule');
        $this->unit->asyncInsert();
    }
}
