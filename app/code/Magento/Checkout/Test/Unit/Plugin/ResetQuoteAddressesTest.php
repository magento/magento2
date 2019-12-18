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
    protected function setUp()
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
        $quoteVisibleItems = [1, 2];

        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->will($this->returnValue($quoteVisibleItems));
        $this->quoteMock->expects($this->never())
            ->method('getAllAddresses')
            ->willReturnSelf();

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, 1);
    }

    /**
     * Test clearing the addresses from an empty quote
     *
     * @dataProvider quoteDataProvider
     * @param bool $isVirtualQuote
     * @param bool $quoteHasAddresses
     * @param $extensionAttributes
     */
    public function testClearingTheAddressesFromEmptyQuote(
        bool $isVirtualQuote,
        bool $quoteHasAddresses,
        $extensionAttributes
    ) {
        $quoteVisibleItems = [];

        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->will($this->returnValue($quoteVisibleItems));

        if ($quoteHasAddresses) {
            $address = $this->createPartialMock(Address::class, ['getId']);

            $address->expects($this->any())
                ->method('getId')
                ->willReturn(1);

            $addresses = [$address];

            $this->quoteMock->expects($this->any())
                ->method('getAllAddresses')
                ->will($this->returnValue($addresses));

            $this->quoteMock->expects($this->exactly(count($addresses)))
                ->method('removeAddress')
                ->willReturnSelf();
        } else {
            $this->quoteMock->expects($this->any())
                ->method('getAllAddresses')
                ->willReturn([]);
        }

        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->quoteMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn($isVirtualQuote);

        if ($isVirtualQuote && $extensionAttributes) {
            $this->extensionAttributesMock->expects($this->any())
                ->method('getShippingAssignments')
                ->willReturn([1]);

            $this->extensionAttributesMock->expects($this->once())
                ->method('setShippingAssignments')
                ->willReturnSelf();
        }

        $this->plugin->afterRemoveItem($this->quoteMock, $this->quoteMock, 1);
    }

    /**
     * Quote information data provider
     *
     * @return array
     */
    public function quoteDataProvider(): array
    {
        return [
            'Test case with virtual quote' => [
                true,
                true,
                null
            ],
            'Test case with virtual quote and without a quote address' => [
                true,
                false,
                null
            ],
            'Test case with a non virtual quote without extension attributes' => [
                false,
                true,
                []
            ],
            'Test case with a non virtual quote with shipping assignments' => [
                false,
                true,
                [1]
            ]
        ];
    }
}
