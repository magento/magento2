<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Test\Unit\Price;

use \Magento\Framework\Pricing\Price\Factory;

/**
 * Test class for \Magento\Framework\Pricing\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Framework\Pricing\Price\Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $quantity = 2.2;
        $className = \Magento\Framework\Pricing\Price\PriceInterface::class;
        $priceMock = $this->getMock($className);
        $saleableItem = $this->getMock(\Magento\Framework\Pricing\SaleableInterface::class);
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['saleableItem' => $saleableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($priceMock));

        $this->assertEquals($priceMock, $this->model->create($saleableItem, $className, $quantity, $arguments));
    }

    /**
     * @codingStandardsIgnoreStart
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Magento\Framework\Pricing\PriceInfo\Base doesn't implement \Magento\Framework\Pricing\Price\PriceInterface
     * @codingStandardsIgnoreEnd
     */
    public function testCreateWithException()
    {
        $quantity = 2.2;
        $className = \Magento\Framework\Pricing\PriceInfo\Base::class;
        $priceMock = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $saleableItem = $this->getMock(\Magento\Framework\Pricing\SaleableInterface::class);
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['saleableItem' => $saleableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($priceMock));

        $this->model->create($saleableItem, $className, $quantity, $arguments);
    }
}
