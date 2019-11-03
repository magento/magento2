<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Quote\Address;

use Magento\OfflineShipping\Model\Quote\Address\FreeShipping;
use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for Magento\OfflineShipping\Model\Quote\Address\FreeShipping class.
 */
class FreeShippingTest extends \PHPUnit\Framework\TestCase
{
    private static $websiteId = 1;

    private static $customerGroupId = 2;

    private static $couponCode = 3;

    private static $storeId = 1;

    /**
     * @var FreeShipping
     */
    private $model;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MockObject|Calculator
     */
    private $calculator;

    protected function setUp()
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->calculator = $this->createMock(Calculator::class);

        $this->model = new FreeShipping(
            $this->storeManager,
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
     * @dataProvider itemsDataProvider
     */
    public function testIsFreeShipping(int $addressFree, int $fItemFree, int $sItemFree, bool $expected)
    {
        $address = $this->getShippingAddress();
        $this->withStore();
        $quote = $this->getQuote($address);
        $fItem = $this->getItem($quote);
        $sItem = $this->getItem($quote);
        $items = [$fItem, $sItem];

        $this->calculator->method('init')
            ->with(self::$websiteId, self::$customerGroupId, self::$couponCode);
        $this->calculator->method('processFreeShipping')
            ->withConsecutive(
                [$fItem],
                [$sItem]
            )
            ->willReturnCallback(
                function () use ($fItem, $sItem, $addressFree, $fItemFree, $sItemFree) {
                    // emulate behavior of cart rule calculator
                    $fItem->getAddress()->setFreeShipping($addressFree);
                    $fItem->setFreeShipping($fItemFree);
                    $sItem->setFreeShipping($sItemFree);
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
    public function itemsDataProvider(): array
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
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->method('getStore')
            ->with(self::$storeId)
            ->willReturn($store);

        $store->method('getWebsiteId')
            ->willReturn(self::$websiteId);
    }

    /**
     * Get mock object for quote entity.
     *
     * @param Address $address
     * @return Quote
     */
    private function getQuote(Address $address): Quote
    {
        /** @var Quote|MockObject $quote */
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getCouponCode',
                    'getCustomerGroupId',
                    'getShippingAddress',
                    'getStoreId',
                    'isVirtual'
                ]
            )
            ->getMock();

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
            ->setMethods(['beforeSave'])
            ->getMock();

        return $address;
    }

    /**
     * Gets stub object for quote item.
     *
     * @param Quote $quote
     * @return Item
     */
    private function getItem(Quote $quote): Item
    {
        /** @var Item|MockObject $item */
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHasChildren'])
            ->getMock();
        $item->setQuote($quote);
        $item->setNoDiscount(0);
        $item->setParentItemId(0);
        $item->method('getHasChildren')
            ->willReturn(0);

        return $item;
    }
}
