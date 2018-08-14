<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type\Multishipping;

use Magento\Framework\ObjectManagerInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderFactory;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderPool;

class PlaceOrderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var PlaceOrderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeOrderPool;

    /**
     * @var PlaceOrderFactory
     */
    private $placeOrderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->placeOrderPool = $this->getMockBuilder(PlaceOrderPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->placeOrderFactory = new PlaceOrderFactory($this->objectManager, $this->placeOrderPool);
    }

    public function testCreate()
    {
        $paymentProviderCode = 'code';

        $placeOrder = $this->getMockForAbstractClass(PlaceOrderInterface::class);
        $this->placeOrderPool->method('get')
            ->with($paymentProviderCode)
            ->willReturn($placeOrder);

        $instance = $this->placeOrderFactory->create($paymentProviderCode);

        $this->assertInstanceOf(PlaceOrderInterface::class, $instance);
    }

    /**
     * Checks that default place order service is created when place order pull returns null.
     */
    public function testCreateWithDefault()
    {
        $paymentProviderCode = 'code';

        $this->placeOrderPool->method('get')
            ->with($paymentProviderCode)
            ->willReturn(null);
        $placeOrder = $this->getMockBuilder(PlaceOrderDefault::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->method('get')
            ->with(PlaceOrderDefault::class)
            ->willReturn($placeOrder);

        $instance = $this->placeOrderFactory->create($paymentProviderCode);

        $this->assertInstanceOf(PlaceOrderDefault::class, $instance);
    }
}
