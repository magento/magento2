<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Test\Unit\Amount;

/**
 * Class AmountFactoryTest
 */
class AmountFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Amount\AmountFactory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\App\ObjectManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountMock;

    /**
     * Test setUp
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $this->amountMock = $this->createMock(\Magento\Framework\Pricing\Amount\Base::class);
        $this->factory = new \Magento\Framework\Pricing\Amount\AmountFactory($this->objectManagerMock);
    }

    /**
     * Test method create
     */
    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Pricing\Amount\AmountInterface::class),
                $this->equalTo(
                    [
                        'amount' => 'this-is-float-amount',
                        'adjustmentAmounts' => ['this-is-array-of-adjustments'],
                    ]
                )
            )
            ->will($this->returnValue($this->amountMock));
        $this->assertEquals(
            $this->amountMock,
            $this->factory->create('this-is-float-amount', ['this-is-array-of-adjustments'])
        );
    }

    /**
     * Test method create
     *
     * @expectedException \InvalidArgumentException
     */
    public function testCreateException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Pricing\Amount\AmountInterface::class),
                $this->equalTo(
                    [
                        'amount' => 'this-is-float-amount',
                        'adjustmentAmounts' => ['this-is-array-of-adjustments'],
                    ]
                )
            )
            ->will($this->returnValue(new \stdClass()));
        $this->assertEquals(
            $this->amountMock,
            $this->factory->create('this-is-float-amount', ['this-is-array-of-adjustments'])
        );
    }
}
