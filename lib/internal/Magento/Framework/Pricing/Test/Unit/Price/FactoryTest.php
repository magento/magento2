<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Price;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Price\Factory;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\Pricing\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $quantity = 2.2;
        $className = PriceInterface::class;
        $priceMock = $this->createMock($className);
        $saleableItem = $this->getMockForAbstractClass(SaleableInterface::class);
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['saleableItem' => $saleableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->willReturn($priceMock);

        $this->assertEquals($priceMock, $this->model->create($saleableItem, $className, $quantity, $arguments));
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testCreateWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Magento\Framework\Pricing\PriceInfo\Base doesn\'t implement '
            . '\Magento\Framework\Pricing\Price\PriceInterface'
        );
        $quantity = 2.2;
        $className = Base::class;
        $priceMock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
        $saleableItem = $this->getMockForAbstractClass(SaleableInterface::class);
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['saleableItem' => $saleableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->willReturn($priceMock);

        $this->model->create($saleableItem, $className, $quantity, $arguments);
    }
}
