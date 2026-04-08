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
            ->with($this->getQuote($this->getShippingAddress()));
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
     * Get mock object for quote entity.
     *
     * @param Address $address
     * @return Quote|MockObject
     */
    private function getQuote(Address $address): Quote
    {
        /** @var Quote|MockObject $quote */
        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            [
                'getCustomerGroupId',
                'getShippingAddress',
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
     * Gets stub object for quote item.
     *
     * @param Quote $quote
     * @param Address $address
     * @return Item|MockObject
     */
    private function getItem(Quote $quote, Address $address): Item
    {
        /** @var Item|MockObject $item */
        $item = $this->createPartialMockWithReflection(
            Item::class,
            ['getHasChildren',
            'setQuote',
            'setNoDiscount',
            'setParentItemId',
            'getAddress',
            'setFreeShipping',
            'getFreeShipping']
        );
        $item->method('setQuote')->willReturnSelf();
        $item->method('setNoDiscount')->willReturnSelf();
        $item->method('setParentItemId')->willReturnSelf();
        $item->method('getHasChildren')
            ->willReturn(0);
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
