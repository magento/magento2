<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Total\Config;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Sales\Model\Order\Total\Config\Base */
    private $object;

    /** @var  SerializerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $serializer;

    /** @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit\Framework\MockObject\MockObject */
    private $configCacheType;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var \Magento\Sales\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    private $salesConfig;

    /** @var \Magento\Sales\Model\Order\TotalFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $orderTotalFactory;

    protected function setUp(): void
    {
        $this->configCacheType = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->salesConfig = $this->createMock(\Magento\Sales\Model\Config::class);
        $this->orderTotalFactory = $this->createMock(\Magento\Sales\Model\Order\TotalFactory::class);
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
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->willReturn(
            [
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1723],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
            ->willReturn($total);

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
     */
    public function testGetTotalModelsInvalidTotalModel()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The total model should be extended from \\Magento\\Sales\\Model\\Order\\Total\\AbstractTotal.');

        $this->salesConfig->expects($this->once())->method('getGroupTotals')->willReturn(
            [
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1723],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
            ->willReturn($this);

        $this->object->getTotalModels();
    }

    public function testGetTotalUnserializeCachedCollectorCodes()
    {
        $total = $this->getMockForAbstractClass(\Magento\Sales\Model\Order\Total\AbstractTotal::class);
        $this->salesConfig->expects($this->any())->method('getGroupTotals')->willReturn(
            [
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1723],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
            ->willReturn($total);

        $sortedCodes = ['other_code', 'some_code'];
        $serializedCodes = '["other_code", "some_code"]';
        $this->configCacheType->expects($this->once())->method('load')->with('sorted_collectors')
            ->willReturn($serializedCodes);
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
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->willReturn(
            [
                'some_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1903],
                'other_code' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 1112],
                'big_order' =>
                    ['instance' => \Magento\Sales\Model\Order\Total\AbstractTotal::class, 'sort_order' => 3000],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(\Magento\Sales\Model\Order\Total\AbstractTotal::class)
            ->willReturn($total);

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
