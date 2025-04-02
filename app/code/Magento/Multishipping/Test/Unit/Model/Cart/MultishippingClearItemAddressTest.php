<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Cart;

use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Controller\Sidebar\UpdateItemQty;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\Cart\MultishippingClearItemAddress;
use Magento\Multishipping\Model\DisableMultishipping;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test shipping addresses and item assignments after MultiShipping flow
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultishippingClearItemAddressTest extends TestCase
{
    /**
     * @var MultishippingClearItemAddress
     */
    private $model;

    /**
     * @var MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var CartModel|MockObject
     */
    private $cartMock;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->addressRepositoryMock = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $disableMultishippingMock = $this->createMock(DisableMultishipping::class);
        $this->cartMock = $this->createMock(CartModel::class);
        $this->model = new MultishippingClearItemAddress(
            $this->cartRepositoryMock,
            $this->checkoutSessionMock,
            $this->addressRepositoryMock,
            $disableMultishippingMock,
            $this->cartMock
        );
    }

    /**
     * Test cart and mini cart plugin
     *
     * @param string $actionName
     * @param int $addressId
     * @param int $customerAddressId
     * @param bool $isMultiShippingAddresses
     * @throws LocalizedException
     * @dataProvider getDataDataProvider
     */
    public function testClearAddressItem(
        string $actionName,
        int $addressId,
        int $customerAddressId,
        bool $isMultiShippingAddresses
    ): void {
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $quoteMock = $this->createPartialMock(Quote::class, [
            'isMultipleShippingAddresses',
            'getAllShippingAddresses',
            'removeAddress',
            'getShippingAddress',
            'getCustomer'
        ]);
        $requestMock->method('getActionName')
            ->willReturn($actionName);
        $this->checkoutSessionMock->method('getQuote')
            ->willReturn($quoteMock);
        $this->checkoutSessionMock->method('clearQuote')
            ->willReturnSelf();
        $addressMock = $this->createMock(Address::class);
        $addressMock->method('getId')
            ->willReturn($addressId);

        $quoteMock->method('isMultipleShippingAddresses')
            ->willReturn($isMultiShippingAddresses);
        $quoteMock->method('getAllShippingAddresses')
            ->willReturn([$addressMock]);
        $quoteMock->method('removeAddress')
            ->with($addressId)->willReturnSelf();

        $shippingAddressMock = $this->createMock(Address::class);
        $quoteMock->method('getShippingAddress')
            ->willReturn($shippingAddressMock);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $quoteMock->method('getCustomer')
            ->willReturn($customerMock);
        $customerMock->method('getDefaultShipping')
            ->willReturn($customerAddressId);

        $customerAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->addressRepositoryMock->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $shippingAddressMock->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();

        $this->cartRepositoryMock->expects($this->any())
            ->method('save')
            ->with($quoteMock);
        if ($actionName instanceof UpdateItemQty) {
            $quoteMock->expects($this->any())->method('getId')->willReturnSelf();
            $this->cartRepositoryMock->expects($this->any())
                ->method('get')->with($quoteMock)->willReturn($quoteMock);
            $this->cartMock->expects($this->any())->method('setQuote')->with($quoteMock);
        }
        $this->model->clearAddressItem(
            $this->createMock(Cart::class),
            $requestMock
        );
    }

    /**
     * @return array
     */
    public static function getDataDataProvider()
    {
        return [
            'test with `add` action and multi shipping address enabled' => ['add', 100, 200, true],
            'test with `add` action and multi shipping address disabled' => ['add', 100, 200, false],
            'test with `edit` action and multi shipping address disabled' => ['add', 110, 200, false]
        ];
    }
}
