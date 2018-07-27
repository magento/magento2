<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Block\Checkout;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Block\Checkout\Shipping
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $checkoutSession = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);

        $this->model = $objectManager->getObject(
            'Magento\Tax\Block\Checkout\Shipping',
            ['checkoutSession' => $checkoutSession]
        );
    }

    /**
     * @param string $shippingMethod
     * @param bool $expectedResult
     * @dataProvider displayShippingDataProvider
     */
    public function testDisplayShipping($shippingMethod, $expectedResult)
    {
        $addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', ['getShippingMethod'], [], '', false);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);

        $this->assertEquals($expectedResult, $this->model->displayShipping());
    }

    /**
     * @return array
     */
    public function displayShippingDataProvider()
    {
        return [
            ["flatrate_flatrate", true],
            [null, false]
        ];
    }
}
