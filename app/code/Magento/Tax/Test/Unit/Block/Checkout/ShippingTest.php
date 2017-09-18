<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Block\Checkout;

class ShippingTest extends \PHPUnit\Framework\TestCase
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
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);

        $this->model = $objectManager->getObject(
            \Magento\Tax\Block\Checkout\Shipping::class,
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
        $addressMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, ['getShippingMethod']);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);

        $this->assertEquals($expectedResult, $this->model->displayShipping());
    }

    public function displayShippingDataProvider()
    {
        return [
            ["flatrate_flatrate", true],
            [null, false]
        ];
    }
}
