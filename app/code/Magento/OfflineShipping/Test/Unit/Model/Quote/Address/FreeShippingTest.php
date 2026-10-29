<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Quote\Address;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\OfflineShipping\Model\Quote\Address\FreeShipping;
use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\OfflineShipping\Model\Quote\Address\FreeShipping class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FreeShippingTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var int
     */
    private static $websiteId = 1;

    /**
     * @var int
     */
    private static $customerGroupId = 2;

    /**
     * @var int
     */
    private static $couponCode = 3;

    /**
     * @var int
     */
    private static $storeId = 1;

    /**
     * @var FreeShipping
     */
    private $model;

    /**
     * @var MockObject|Calculator
     */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = $this->createMock(Calculator::class);

        $this->model = new FreeShipping(
            $this->calculator
        );
    }

    /**
     * Checks free shipping availability based on quote items and cart rule calculations.
     *
     * @param int $addressFree
     * @param int $fItemFree
     * @param int $sItemFree
     * @param bool $expected
     */
    #[DataProvider('itemsDataProvider')]
    public function testIsFreeShipping(int $addressFree, int $fItemFree, int $sItemFree, bool $expected)
    {
        $address = $this->getShippingAddress();
        $this->withStore();
        $quote = $this->getQuote($address);
        $fItem = $this->getItem($quote, $address);
        $sItem = $this->getItem($quote, $address);
        $items = [$fItem, $sItem];

        $this->calculator->method('initFromQuote')
            ->with($quote);
        $this->calculator->method('processFreeShipping')
            ->willReturnCallback(
                function ($arg1) use ($fItem, $sItem, $addressFree, $fItemFree, $sItemFree) {
                    if ($arg1 === $fItem) {
                        $fItem->getAddress()->setFreeShipping($addressFree);
                        $fItem->setFreeShipping($fItemFree);
                    } elseif ($arg1 === $sItem) {
                        $sItem->setFreeShipping($sItemFree);
                    }
                }
            );

        $actual = $this->model->isFreeShipping($quote, $items);
        self::assertEquals($expected, $actual);
        self::assertEquals($expected, $address->getFreeShipping());
    }

    /**
     * Free shipping must be propagated to calculated child items.
     *
     * @param bool $isFreeShipping
     * @param int $expectedChildFreeShipping
     * @return void
     */
    #[DataProvider('calculatedChildrenDataProvider')]
    public function testIsFreeShippingAppliesFreeShippingToCalculatedChildren(
        bool $isFreeShipping,
        int $expectedChildFreeShipping
    ): void {
        $address = $this->getShippingAddress();
        $quote = $this->getQuote($address);
        $childItem = $this->getItem($quote, $address);
        $parentItem = $this->getParentItemWithChildren($quote, $address, [$childItem]);
        $items = [$parentItem];

        $this->calculator->method('initFromQuote')->with($quote);
        $this->calculator->expects($this->exactly(2))
            ->method('processFreeShipping')
            ->willReturnCallback(
                function ($item) use ($parentItem, $childItem, $isFreeShipping) {
                    if ($item === $parentItem) {
                        $parentItem->setFreeShipping($isFreeShipping);
                    } elseif ($item === $childItem) {
                        $childItem->setFreeShipping(0);
                    }
                }
            );

        $actual = $this->model->isFreeShipping($quote, $items);

        self::assertEquals($isFreeShipping, $actual);
        self::assertEquals($expectedChildFreeShipping, $childItem->getFreeShipping());
    }

    /**
     * @return void
     */
    public function testIsFreeShippingReturnsFalseWhenNoItems(): void
    {
        $quote = $this->createMock(Quote::class);

        $this->calculator->expects($this->never())->method('initFromQuote');

        self::assertFalse($this->model->isFreeShipping($quote, []));
    }

    /**
     * @return void
     */
    public function testIsFreeShippingSkipsItemsWithNoDiscount(): void
    {
        $address = $this->getShippingAddress();
        $quote = $this->getQuote($address);
        $noDiscountItem = $this->getItem($quote, $address, 1);
        $noDiscountItem->setFreeShipping(1);
        $items = [$noDiscountItem];

        $this->calculator->method('initFromQuote')->with($quote);
        $this->calculator->expects($this->never())->method('processFreeShipping');

        $actual = $this->model->isFreeShipping($quote, $items);

        self::assertFalse($actual);
        self::assertFalse((bool)$noDiscountItem->getFreeShipping());
        self::assertEquals(0, $address->getFreeShipping());
    }

    /**
     * @return void
     */
    public function testIsFreeShippingSkipsChildItemsWithParentItemId(): void
    {
        $address = $this->getShippingAddress();
        $quote = $this->getQuote($address);
        $childItem = $this->getItem($quote, $address, 0, 1);
        $parentItem = $this->getItem($quote, $address);
        $items = [$childItem, $parentItem];

        $this->calculator->method('initFromQuote')->with($quote);
        $this->calculator->expects($this->once())
            ->method('processFreeShipping')
            ->with($parentItem)
            ->willReturnCallback(
                function ($item) use ($parentItem) {
                    $parentItem->setFreeShipping(1);
                }
            );

        $actual = $this->model->isFreeShipping($quote, $items);

        self::assertTrue($actual);
        self::assertEquals(1, $parentItem->getFreeShipping());
    }

    /**
     * Virtual quote items on billing address must keep item-level free shipping when address flag is stale.
     *
     * @return void
     */
    public function testIsFreeShippingWithStaleBillingAddressFreeShippingFlag(): void
    {
        $shippingAddress = $this->getShippingAddress();
        $billingAddress = $this->getShippingAddress();
        $billingAddress->setFreeShipping(1);

        $quote = $this->getVirtualQuote($shippingAddress, $billingAddress);
        $firstItem = $this->getItem($quote, $billingAddress);
        $secondItem = $this->getItem($quote, $billingAddress);
        $items = [$firstItem, $secondItem];

        $this->calculator->method('initFromQuote')->with($quote);
        $this->calculator->method('processFreeShipping')
            ->willReturnCallback(
                function ($item) {
                    $item->setFreeShipping(1);
                }
            );

        $actual = $this->model->isFreeShipping($quote, $items);

        self::assertTrue($actual);
        self::assertEquals(1, $firstItem->getFreeShipping());
        self::assertEquals(1, $secondItem->getFreeShipping());
    }

    /**
     * Gets list of variations for calculated child items.
     *
     * @return array
     */
    public static function calculatedChildrenDataProvider(): array
    {
        return [
            'free_shipping_applied_to_children' => [
                'isFreeShipping' => true,
                'expectedChildFreeShipping' => 1,
            ],
            'free_shipping_not_applied_to_children' => [
                'isFreeShipping' => false,
                'expectedChildFreeShipping' => 0,
            ],
        ];
    }

    /**
     * Gets list of variations with free shipping availability.
     *
     * @return array
     */
    public static function itemsDataProvider(): array
    {
        return [
            ['addressFree' => 1, 'fItemFree' => 0, 'sItemFree' => 0, 'expected' => true],
            ['addressFree' => 0, 'fItemFree' => 1, 'sItemFree' => 0, 'expected' => false],
            ['addressFree' => 0, 'fItemFree' => 0, 'sItemFree' => 1, 'expected' => false],
            ['addressFree' => 0, 'fItemFree' => 1, 'sItemFree' => 1, 'expected' => true],
        ];
    }

    /**
     * Creates mock object for store entity.
     */
    private function withStore()
    {
        $store = $this->createMock(StoreInterface::class);

        $store->method('getWebsiteId')
            ->willReturn(self::$websiteId);
    }

    /**
     * Get mock object for virtual quote entity.
     *
     * @param Address $shippingAddress
     * @param Address $billingAddress
     * @return Quote|MockObject
     */
    private function getVirtualQuote(Address $shippingAddress, Address $billingAddress): Quote
    {
        /** @var Quote|MockObject $quote */
        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            [
                'getCustomerGroupId',
                'getShippingAddress',
                'getBillingAddress',
                'getStoreId',
                'isVirtual',
                'getCouponCode'
            ]
        );

        $quote->method('getStoreId')
            ->willReturn(self::$storeId);
        $quote->method('getCustomerGroupId')
            ->willReturn(self::$customerGroupId);
        $quote->method('getCouponCode')
            ->willReturn(self::$couponCode);
        $quote->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $quote->method('getBillingAddress')
            ->willReturn($billingAddress);
        $quote->method('isVirtual')
            ->willReturn(true);

        return $quote;
    }

    /**
     * Get mock object for quote entity.
     *
     * @param Address $address
     * @return Quote|MockObject
     */
    private function getQuote(Address $address): Quote
    {
        $billingAddress = $this->getShippingAddress();

        /** @var Quote|MockObject $quote */
        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            [
                'getCustomerGroupId',
                'getShippingAddress',
                'getBillingAddress',
                'getStoreId',
                'isVirtual',
                'getCouponCode'
            ]
        );

        $quote->method('getStoreId')
            ->willReturn(self::$storeId);
        $quote->method('getCustomerGroupId')
            ->willReturn(self::$customerGroupId);
        $quote->method('getCouponCode')
            ->willReturn(self::$couponCode);
        $quote->method('getShippingAddress')
            ->willReturn($address);
        $quote->method('getBillingAddress')
            ->willReturn($billingAddress);
        $quote->method('isVirtual')
            ->willReturn(false);

        return $quote;
    }

    /**
     * Gets stub object for shipping address.
     *
     * @return Address|MockObject
     */
    private function getShippingAddress(): Address
    {
        /** @var Address|MockObject $address */
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['beforeSave'])
            ->getMock();

        return $address;
    }

    /**
     * Gets stub object for configurable parent quote item with calculated children.
     *
     * @param Quote $quote
     * @param Address $address
     * @param Item[] $children
     * @return Item|MockObject
     */
    private function getParentItemWithChildren(Quote $quote, Address $address, array $children): Item
    {
        /** @var Item|MockObject $item */
        $item = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getHasChildren',
                'isChildrenCalculated',
                'getChildren',
                'setQuote',
                'setNoDiscount',
                'setParentItemId',
                'getAddress',
                'setFreeShipping',
                'getFreeShipping'
            ]
        );
        $item->method('setQuote')->willReturnSelf();
        $item->method('setNoDiscount')->willReturnSelf();
        $item->method('setParentItemId')->willReturnSelf();
        $item->method('getHasChildren')->willReturn(true);
        $item->method('isChildrenCalculated')->willReturn(true);
        $item->method('getChildren')->willReturn($children);
        $item->method('getAddress')->willReturn($address);

        $freeShipping = 0;
        $item->method('setFreeShipping')->willReturnCallback(function ($value) use (&$freeShipping, $item) {
            $freeShipping = $value;
            return $item;
        });
        $item->method('getFreeShipping')->willReturnCallback(function () use (&$freeShipping) {
            return $freeShipping;
        });

        return $item;
    }

    /**
     * Gets stub object for quote item.
     *
     * @param Quote $quote
     * @param Address $address
     * @param int $noDiscount
     * @param int|null $parentItemId
     * @return Item|MockObject
     */
    private function getItem(
        Quote $quote,
        Address $address,
        int $noDiscount = 0,
        ?int $parentItemId = null
    ): Item {
        /** @var Item|MockObject $item */
        $item = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getHasChildren',
                'getNoDiscount',
                'getParentItemId',
                'setQuote',
                'setNoDiscount',
                'setParentItemId',
                'getAddress',
                'setFreeShipping',
                'getFreeShipping'
            ]
        );
        $item->method('setQuote')->willReturnSelf();
        $item->method('setNoDiscount')->willReturnSelf();
        $item->method('setParentItemId')->willReturnSelf();
        $item->method('getHasChildren')
            ->willReturn(0);
        $item->method('getNoDiscount')
            ->willReturn($noDiscount);
        $item->method('getParentItemId')
            ->willReturn($parentItemId);
        $item->method('getAddress')
            ->willReturn($address);

        $freeShipping = 0;
        $item->method('setFreeShipping')->willReturnCallback(function ($value) use (&$freeShipping, $item) {
            $freeShipping = $value;
            return $item;
        });
        $item->method('getFreeShipping')->willReturnCallback(function () use (&$freeShipping) {
            return $freeShipping;
        });

        return $item;
    }
}
