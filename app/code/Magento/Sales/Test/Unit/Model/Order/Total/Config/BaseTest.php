<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Total\Config;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Order\Total\Config\Base */
    private $object;

    /** @var  SerializerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    /** @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject */
    private $configCacheType;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var \Magento\Sales\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    private $salesConfig;

    /** @var \Magento\Sales\Model\Order\TotalFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $orderTotalFactory;

    protected function setUp()
    {
        $this->configCacheType = $this->getMock(\Magento\Framework\App\Cache\Type\Config::class, [], [], '', false);
        $this->logger = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->salesConfig = $this->getMock(\Magento\Sales\Model\Config::class, [], [], '', false);
        $this->orderTotalFactory = $this->getMock(\Magento\Sales\Model\Order\TotalFactory::class, [], [], '', false);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Sales\Model\Order\Total\Config\Base::class,
            [
                'configCacheType' => $this->configCacheType,
                'logger' => $this->logger,
                'salesConfig' => $this->salesConfig,
                'orderTotalFactory' => $this->orderTotalFactory,
                'serializer' => $this->serializer,
            ]
        );
    }

    public function testGetTotalModels()
    {
        $total = $this->getMockForAbstractClass(\Magento\Sales\Model\Order\Total\AbstractTotal::class);
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
            ->will($this->returnValue($total));

        $sortedCodes = ['other_code', 'some_code'];
        $serializedCodes = '["other_code", "some_code"]';
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($sortedCodes)
            ->willReturn($serializedCodes);
        $this->configCacheType->expects($this->once())->method('save')
            ->with($serializedCodes, 'sorted_collectors');

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
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
            ->will($this->returnValue($this));

        $this->object->getTotalModels();
    }

    public function testGetTotalUnserializeCachedCollectorCodes()
    {
        $total = $this->getMockForAbstractClass(\Magento\Sales\Model\Order\Total\AbstractTotal::class);
        $this->salesConfig->expects($this->any())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
            ->will($this->returnValue($total));

        $sortedCodes = ['other_code', 'some_code'];
        $serializedCodes = '["other_code", "some_code"]';
        $this->configCacheType->expects($this->once())->method('load')->with('sorted_collectors')
            ->will($this->returnValue($serializedCodes));
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedCodes)
            ->willReturn($sortedCodes);
        $this->configCacheType->expects($this->never())->method('save');

        $this->assertSame(
            ['other_code' => $total, 'some_code' => $total],
            $this->object->getTotalModels()
        );
    }

    public function testGetTotalModelsSortingSubroutine()
    {
        $total = $this->getMockForAbstractClass(\Magento\Sales\Model\Order\Total\AbstractTotal::class);
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1112],
                'big_order' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 3000],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
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
