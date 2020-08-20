<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Total\Config;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Total\AbstractTotal;
use Magento\Sales\Model\Order\Total\Config\Base;
use Magento\Sales\Model\Order\TotalFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BaseTest extends TestCase
{
    /** @var Base */
    private $object;

    /** @var  SerializerInterface|MockObject */
    private $serializer;

    /** @var Config|MockObject */
    private $configCacheType;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var \Magento\Sales\Model\Config|MockObject */
    private $salesConfig;

    /** @var TotalFactory|MockObject */
    private $orderTotalFactory;

    protected function setUp(): void
    {
        $this->configCacheType = $this->createMock(Config::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->salesConfig = $this->createMock(\Magento\Sales\Model\Config::class);
        $this->orderTotalFactory = $this->createMock(TotalFactory::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            Base::class,
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
        $total = $this->getMockForAbstractClass(AbstractTotal::class);
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->willReturn(
            [
                'some_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1903],
                'other_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1723],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(AbstractTotal::class)
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

    public function testGetTotalModelsInvalidTotalModel()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The total model should be extended from \Magento\Sales\Model\Order\Total\AbstractTotal.'
        );
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->willReturn(
            [
                'some_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1903],
                'other_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1723],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(AbstractTotal::class)
            ->willReturn($this);

        $this->object->getTotalModels();
    }

    public function testGetTotalUnserializeCachedCollectorCodes()
    {
        $total = $this->getMockForAbstractClass(AbstractTotal::class);
        $this->salesConfig->expects($this->any())->method('getGroupTotals')->willReturn(
            [
                'some_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1903],
                'other_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1723],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(AbstractTotal::class)
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
        $total = $this->getMockForAbstractClass(AbstractTotal::class);
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->willReturn(
            [
                'some_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1903],
                'other_code' => ['instance' => AbstractTotal::class, 'sort_order' => 1112],
                'big_order' => ['instance' => AbstractTotal::class, 'sort_order' => 3000],
            ]
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with(AbstractTotal::class)
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
