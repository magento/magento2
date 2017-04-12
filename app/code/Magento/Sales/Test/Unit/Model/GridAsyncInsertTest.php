<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model;

/**
 * Class GridAsyncInsertTest
 */
class GridAsyncInsertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\GridAsyncInsert
     */
    protected $unit;

    /**
     * @var \Magento\Sales\Model\ResourceModel\GridInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gridAggregatorMock;

    /**
     * @var \Magento\Sales\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesModelMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigurationMock;

    protected function setUp()
    {
        $this->gridAggregatorMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\GridInterface::class)
            ->getMockForAbstractClass();
        $this->salesModelMock = $this->getMockBuilder(\Magento\Sales\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId'
                ]
            )
            ->getMockForAbstractClass();
        $this->scopeConfigurationMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->unit = new \Magento\Sales\Model\GridAsyncInsert(
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
