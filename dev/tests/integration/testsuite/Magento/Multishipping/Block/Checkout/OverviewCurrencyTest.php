<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Multishipping\Block\Checkout;

use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class OverviewCurrencyTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var StoreManagerInterface */
    private $storeManager;

    protected function setUp(): void
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoConfigFixture default_store currency/options/base USD
     * @magentoConfigFixture default_store currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD
     */
    public function testGetShippingPriceInclTaxSameCurrency(): void
    {
        $store = $this->storeManager->getStore();

        $quote = $this->objectManager->create(Quote::class);
        $quote->setStore($store);

        $multishipping = $this->getMockBuilder(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->getMock();
        $multishipping->method('getQuote')->willReturn($quote);

        $block = $this->objectManager->create(Overview::class, ['multishipping' => $multishipping]);

        $address = $this->objectManager->create(Address::class);
        $address->setShippingMethod('flatrate_flatrate');
        $address->setShippingTaxAmount(2.5);

        $rate = $this->objectManager->create(Rate::class);
        $rate->setCode('flatrate_flatrate');
        $rate->setPrice(10.0);
        $address->addShippingRate($rate);

        $result = $block->getShippingPriceInclTax($address);

        /** @var PriceCurrencyInterface $priceCurrency */
        $priceCurrency = $this->objectManager->get(PriceCurrencyInterface::class);
        $expected = $priceCurrency->format(12.5, true, PriceCurrencyInterface::DEFAULT_PRECISION, $store);

        $this->assertSame($expected, $result);
    }

    /**
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate_rollback.php
     * @magentoConfigFixture default_store currency/options/base USD
     * @magentoConfigFixture default_store currency/options/default CNY
     * @magentoConfigFixture default_store currency/options/allow USD,CNY
     */
    public function testGetShippingPriceInclTaxDifferentCurrency(): void
    {
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['convert'])
            ->getMock();
        $currency->method('convert')->with(12.0, 'CNY')->willReturn(12.0);

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBaseCurrencyCode', 'getCurrentCurrencyCode', 'getBaseCurrency'])
            ->getMock();
        $store->method('getBaseCurrencyCode')->willReturn('USD');
        $store->method('getCurrentCurrencyCode')->willReturn('CNY');
        $store->method('getBaseCurrency')->willReturn($currency);

        $quote = $this->objectManager->create(Quote::class);
        $quote->setStore($store);

        $multishipping = $this->getMockBuilder(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->getMock();
        $multishipping->method('getQuote')->willReturn($quote);

        $block = $this->objectManager->create(Overview::class, ['multishipping' => $multishipping]);

        $address = $this->objectManager->create(Address::class);
        $address->setShippingMethod('flatrate_flatrate');
        $address->setBaseShippingTaxAmount(2.0);

        $rate = $this->objectManager->create(Rate::class);
        $rate->setCode('flatrate_flatrate');
        $rate->setPrice(10.0);
        $address->addShippingRate($rate);

        $expected = $block->formatPrice(10.0);

        $this->assertSame($expected, $block->getShippingPriceInclTax($address));
    }

    /**
     * @magentoConfigFixture default_store currency/options/base USD
     * @magentoConfigFixture default_store currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD
     */
    public function testGetShippingPriceExclTaxSameCurrency(): void
    {
        $store = $this->storeManager->getStore();

        $quote = $this->objectManager->create(Quote::class);
        $quote->setStore($store);

        $multishipping = $this->getMockBuilder(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->getMock();
        $multishipping->method('getQuote')->willReturn($quote);

        $block = $this->objectManager->create(Overview::class, ['multishipping' => $multishipping]);

        $address = $this->objectManager->create(Address::class);
        $address->setShippingMethod('flatrate_flatrate');

        $rate = $this->objectManager->create(Rate::class);
        $rate->setCode('flatrate_flatrate');
        $rate->setPrice(15.75);
        $address->addShippingRate($rate);

        $expected = $block->formatPrice(15.75);
        $this->assertSame($expected, $block->getShippingPriceExclTax($address));
    }

    /**
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate_rollback.php
     * @magentoConfigFixture default_store currency/options/base USD
     * @magentoConfigFixture default_store currency/options/default CNY
     * @magentoConfigFixture default_store currency/options/allow USD,CNY
     */
    public function testGetShippingPriceExclTaxDifferentCurrency(): void
    {
        $store = $this->storeManager->getStore();
        $store->setCurrentCurrencyCode('CNY');

        $quote = $this->objectManager->create(Quote::class);
        $quote->setStore($store);

        $multishipping = $this->getMockBuilder(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->getMock();
        $multishipping->method('getQuote')->willReturn($quote);

        $block = $this->objectManager->create(Overview::class, ['multishipping' => $multishipping]);

        $address = $this->objectManager->create(Address::class);
        $address->setShippingMethod('flatrate_flatrate');

        $rate = $this->objectManager->create(Rate::class);
        $rate->setCode('flatrate_flatrate');
        $rate->setPrice(20.0);
        $address->addShippingRate($rate);

        $converted = $store->getBaseCurrency()->convert(20.0, $store->getCurrentCurrencyCode());
        $expected = $block->formatPrice($converted);

        $this->assertSame($expected, $block->getShippingPriceExclTax($address));
    }
}
