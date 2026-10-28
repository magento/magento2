<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Block\Checkout\Shipping;
use PHPUnit\Framework\Attributes\DataProvider;
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
        // Mock ObjectManager to prevent initialization errors
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        AppObjectManager::setInstance($objectManagerMock);

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
     * @param string|null $shippingMethod
     * @param bool $expectedResult
     */
    #[DataProvider('displayShippingDataProvider')]
    public function testDisplayShipping(?string $shippingMethod, bool $expectedResult): void
    {
        $addressMock = $this->createPartialMock(Address::class, ['getShippingMethod']);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);

        $this->assertEquals($expectedResult, $this->model->displayShipping());
    }

    /**
     * @return array<string, array<int, string|bool|null>>
     */
    public static function displayShippingDataProvider(): array
    {
        return [
            ["flatrate_flatrate", true],
            [null, false]
        ];
    }
}
