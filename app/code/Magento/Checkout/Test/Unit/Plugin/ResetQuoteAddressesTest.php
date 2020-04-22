<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Plugin;

use Magento\Checkout\Plugin\Model\Quote\ResetQuoteAddresses;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ResetQuoteAddressesTest
 *
 * Test of clearing quote addresses after all items were removed.
 */
class ResetQuoteAddressesTest extends TestCase
{
    /**
     * @var int
     */
    private const STUB_ADDRESS_ID = 1;

    /**
     * @var int
     */
    private const STUB_ITEM_ID = 1;

    /**
     * @var int
     */
    private const STUB_SHIPPING_ASSIGNMENTS = 1;

    /**
     * @var array
     */
    private const STUB_QUOTE_ITEMS = [1, 2];

    /**
     * @var ResetQuoteAddresses
     */
    private $plugin;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var CartExtensionInterface|MockObject
     */
    private $extensionAttributesMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->quoteMock = $this->createPartialMock(Quote::class, [
            'getAllAddresses',
            'getAllVisibleItems',
            'removeAddress',
            'getExtensionAttributes',
            'isVirtual',
        ]);
        $this->extensionAttributesMock = $this->getMockBuilder(CartExtensionInterface::class)
            ->setMethods(
                [
                    'getShippingAssignments',
                    'setShippingAssignments'
                ]
            )
            ->getMockForAbstractClass();

        $this->plugin = new ResetQuoteAddresses();
    }

    /**
     * Test removing the addresses from a non empty quote
     */
    public function testRemovingTheAddressesFromNonEmptyQuote()
    {
        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn(static::STUB_QUOTE_ITEMS);
        $this->quoteMock->expects($this->never())
            ->method('getAllAddresses')
            ->willReturnSelf();

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, 1);
    }

    /**
     * Test clearing the addresses from an empty quote with addresses
     *
     * @dataProvider quoteAddressesDataProvider
     *
     * @param bool $isVirtualQuote
     * @param array $extensionAttributes
     */
    public function testClearingAddressesSuccessfullyFromEmptyQuoteWithAddress(
        bool $isVirtualQuote,
        array $extensionAttributes
    ) {
        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn([]);

        $address = $this->createPartialMock(Address::class, ['getId']);

        $address->expects($this->any())
            ->method('getId')
            ->willReturn(static::STUB_ADDRESS_ID);

        $addresses = [$address];

        $this->quoteMock->expects($this->any())
            ->method('getAllAddresses')
            ->willReturn($addresses);

        $this->quoteMock->expects($this->exactly(count($addresses)))
            ->method('removeAddress')
            ->willReturnSelf();

        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->quoteMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn($isVirtualQuote);

        if (!$isVirtualQuote && $extensionAttributes) {
            $this->extensionAttributesMock->expects($this->any())
                ->method('getShippingAssignments')
                ->willReturn([static::STUB_SHIPPING_ASSIGNMENTS]);

            $this->extensionAttributesMock->expects($this->once())
                ->method('setShippingAssignments')
                ->willReturnSelf();
        }

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, static::STUB_ITEM_ID);
    }

    /**
     * Test clearing the addresses from an empty quote
     *
     * @dataProvider quoteNoAddressesDataProvider
     *
     * @param bool $isVirtualQuote
     * @param array $extensionAttributes
     */
    public function testClearingTheAddressesFromEmptyQuote(
        bool $isVirtualQuote,
        array $extensionAttributes
    ) {
        $quoteVisibleItems = [];
        $addresses = [];

        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($quoteVisibleItems);

        $this->quoteMock->expects($this->any())
            ->method('getAllAddresses')
            ->willReturn($addresses);

        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->quoteMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn($isVirtualQuote);

        if (!$isVirtualQuote && $extensionAttributes) {
            $this->extensionAttributesMock->expects($this->any())
                ->method('getShippingAssignments')
                ->willReturn($extensionAttributes);

            $this->extensionAttributesMock->expects($this->once())
                ->method('setShippingAssignments')
                ->willReturnSelf();
        }

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, static::STUB_ITEM_ID);
    }

    /**
     * Quote without address data provider
     *
     * @return array
     */
    public function quoteNoAddressesDataProvider(): array
    {
        return [
            'Test case with virtual quote' => [
                true,
                []
            ],
            'Test case with a non virtual quote without extension attributes' => [
                false,
                []
            ],
            'Test case with a non virtual quote with shipping assignments' => [
                false,
                [1]
            ]
        ];
    }

    /**
     * Quote with address information data provider
     *
     * @return array
     */
    public function quoteAddressesDataProvider(): array
    {
        return [
            'Test case with a virtual quote and no shipping assignments' => [
                true,
                []
            ],
            'Test case with a virtual quote and with shipping assignments' => [
                true,
                [1]
            ],
            'Test case with none virtual quote and with shipping assignments' => [
                false,
                [1]
            ]
        ];
    }
}
