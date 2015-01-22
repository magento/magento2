<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

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

    public function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Framework\Pricing\Price\Factory', [
            'objectManager' => $this->objectManagerMock
        ]);
    }

    public function testCreate()
    {
        $quantity = 2.2;
        $className = 'Magento\Framework\Pricing\Price\PriceInterface';
        $priceMock = $this->getMock($className);
        $saleableItem = $this->getMock('Magento\Framework\Pricing\Object\SaleableInterface');
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
        $className = 'Magento\Framework\Pricing\PriceInfo\Base';
        $priceMock = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $saleableItem = $this->getMock('Magento\Framework\Pricing\Object\SaleableInterface');
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['saleableItem' => $saleableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($priceMock));

        $this->model->create($saleableItem, $className, $quantity, $arguments);
    }
}
