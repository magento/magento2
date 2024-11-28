<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Block\Checkout\Shipping;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingTest extends TestCase
{
    /**
     * @var Shipping
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->quoteMock = $this->createMock(Quote::class);
        $checkoutSession = $this->createMock(Session::class);
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);

        $this->model = $objectManager->getObject(
            Shipping::class,
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
        $addressMock = $this->createPartialMock(Address::class, ['getShippingMethod']);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);

        $this->assertEquals($expectedResult, $this->model->displayShipping());
    }

    /**
     * @return array
     */
    public static function displayShippingDataProvider()
    {
        return [
            ["flatrate_flatrate", true],
            [null, false]
        ];
    }
}
