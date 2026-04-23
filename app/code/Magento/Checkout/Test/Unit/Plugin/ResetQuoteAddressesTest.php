<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Plugin;

use Magento\Checkout\Plugin\Model\Quote\ResetQuoteAddresses;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Class ResetQuoteAddressesTest
 *
 * Test of clearing quote addresses after all items were removed.
 */
class ResetQuoteAddressesTest extends TestCase
{
    use MockCreationTrait;
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
     * @var CartExtensionInterface
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
        $this->extensionAttributesMock = $this->createPartialMockWithReflection(
            CartExtensionInterface::class,
            ['getShippingAssignments', 'setShippingAssignments']
        );

        $this->plugin = new ResetQuoteAddresses();
    }

    /**
     * Test removing the addresses from a non empty quote
     */
    public function testRemovingTheAddressesFromNonEmptyQuote()
    {
        $this->quoteMock->method('getAllVisibleItems')->willReturn(static::STUB_QUOTE_ITEMS);
        $this->quoteMock->expects($this->never())
            ->method('getAllAddresses')
            ->willReturnSelf();

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, 1);
    }

    /**
     * Test clearing the addresses from an empty quote with addresses
     *
     * @param bool $isVirtualQuote
     * @param array $extensionAttributes
     */
    #[DataProvider('quoteAddressesDataProvider')]
    public function testClearingAddressesSuccessfullyFromEmptyQuoteWithAddress(
        bool $isVirtualQuote,
        array $extensionAttributes
    ) {
        $this->quoteMock->method('getAllVisibleItems')->willReturn([]);

        $address = $this->createPartialMock(Address::class, ['getId']);

        $address->method('getId')->willReturn(static::STUB_ADDRESS_ID);

        $addresses = [$address];

        $this->quoteMock->method('getAllAddresses')->willReturn($addresses);

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
            // First call in production code condition check, second call in assertion
            $this->extensionAttributesMock->method('getShippingAssignments')
                ->willReturnOnConsecutiveCalls([static::STUB_SHIPPING_ASSIGNMENTS], []);
            $this->extensionAttributesMock->expects($this->once())
                ->method('setShippingAssignments')
                ->with([]);
        }

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, static::STUB_ITEM_ID);
        if (!$isVirtualQuote && $extensionAttributes) {
            $this->assertSame([], $this->extensionAttributesMock->getShippingAssignments());
        }
    }

    /**
     * Test clearing the addresses from an empty quote
     *
     * @param bool $isVirtualQuote
     * @param array $extensionAttributes
     */
    #[DataProvider('quoteNoAddressesDataProvider')]
    public function testClearingTheAddressesFromEmptyQuote(
        bool $isVirtualQuote,
        array $extensionAttributes
    ) {
        $quoteVisibleItems = [];
        $addresses = [];

        $this->quoteMock->method('getAllVisibleItems')->willReturn($quoteVisibleItems);

        $this->quoteMock->method('getAllAddresses')->willReturn($addresses);

        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->quoteMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn($isVirtualQuote);

        if (!$isVirtualQuote && $extensionAttributes) {
            // First call in production code condition check, second call in assertion
            $this->extensionAttributesMock->method('getShippingAssignments')
                ->willReturnOnConsecutiveCalls($extensionAttributes, []);
            $this->extensionAttributesMock->expects($this->once())
                ->method('setShippingAssignments')
                ->with([]);
        }

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, static::STUB_ITEM_ID);
        if (!$isVirtualQuote && $extensionAttributes) {
            $this->assertSame([], $this->extensionAttributesMock->getShippingAssignments());
        }
    }

    /**
     * Quote without address data provider
     *
     * @return array
     */
    public static function quoteNoAddressesDataProvider(): array
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
    public static function quoteAddressesDataProvider(): array
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
