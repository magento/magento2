<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Total\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Order\Total\Config\Base */
    protected $object;

    /** @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configCacheType;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Sales\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $salesConfig;

    /** @var \Magento\Sales\Model\Order\TotalFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderTotalFactory;

    protected function setUp()
    {
        $this->configCacheType = $this->getMock('Magento\Framework\App\Cache\Type\Config', [], [], '', false);
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->salesConfig = $this->getMock('Magento\Sales\Model\Config', [], [], '', false);
        $this->orderTotalFactory = $this->getMock('Magento\Sales\Model\Order\TotalFactory', [], [], '', false);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject('Magento\Sales\Model\Order\Total\Config\Base', [
            'configCacheType' => $this->configCacheType,
            'logger' => $this->logger,
            'salesConfig' => $this->salesConfig,
            'orderTotalFactory' => $this->orderTotalFactory,
        ]);
    }

    public function testGetTotalModels()
    {
        $total = $this->getMockForAbstractClass('Magento\Sales\Model\Order\Total\AbstractTotal');
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($total));

        $this->configCacheType->expects($this->once())->method('save')
            ->with('a:2:{i:0;s:10:"other_code";i:1;s:9:"some_code";}', 'sorted_collectors');

        $this->assertSame(
            ['other_code' => $total, 'some_code' => $total],
            $this->object->getTotalModels()
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The total model should be extended from \Magento\Sales\Model\Order\Total\AbstractTotal.
     */
    public function testGetTotalModelsInvalidTotalModel()
    {
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($this));

        $this->object->getTotalModels();
    }

    public function testGetTotalUnserializeCachedCollectorCodes()
    {
        $total = $this->getMockForAbstractClass('Magento\Sales\Model\Order\Total\AbstractTotal');
        $this->salesConfig->expects($this->any())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($total));

        $this->configCacheType->expects($this->once())->method('load')->with('sorted_collectors')
            ->will($this->returnValue('a:2:{i:0;s:10:"other_code";i:1;s:9:"some_code";}'));
        $this->configCacheType->expects($this->never())->method('save');

        $this->assertSame(
            ['other_code' => $total, 'some_code' => $total],
            $this->object->getTotalModels()
        );
    }

    public function testGetTotalModelsSortingSubroutine()
    {
        $total = $this->getMockForAbstractClass('Magento\Sales\Model\Order\Total\AbstractTotal');
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1112],
                'big_order' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 3000],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($total));

        $this->assertSame(
            [
                'other_code' => $total,
                'some_code' => $total,
                'big_order' => $total,
            ],
            $this->object->getTotalModels()
        );
    }
}
