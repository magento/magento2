<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceCurrencyTest extends TestCase
{
    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory|MockObject
     */
    protected $currencyFactory;

    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->priceCurrency = $objectManager->getObject(
            PriceCurrency::class,
            [
                'storeManager' => $this->storeManager,
                'currencyFactory' => $this->currencyFactory
            ]
        );
    }

    public function testConvert()
    {
        $amount = 5.6;
        $convertedAmount = 9.3;

        $currency = $this->getCurrentCurrencyMock();
        $baseCurrency = $this->getBaseCurrencyMock($amount, $convertedAmount, $currency);
        $store = $this->getStoreMock($baseCurrency);

        $this->assertEquals($convertedAmount, $this->priceCurrency->convert($amount, $store, $currency));
    }

    public function testConvertWithStoreCode()
    {
        $amount = 5.6;
        $storeCode = 2;
        $convertedAmount = 9.3;

        $currency = $this->getCurrentCurrencyMock();
        $baseCurrency = $this->getBaseCurrencyMock($amount, $convertedAmount, $currency);
        $store = $this->getStoreMock($baseCurrency);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with($storeCode)
            ->willReturn($store);

        $this->assertEquals($convertedAmount, $this->priceCurrency->convert($amount, $storeCode, $currency));
    }

    public function testConvertWithCurrencyString()
    {
        $amount = 5.6;
        $currency = 'ru';
        $convertedAmount = 9.3;

        $currentCurrency = $this->getCurrentCurrencyMock();
        $currentCurrency->expects($this->once())
            ->method('load')
            ->with($currency)->willReturnSelf();

        $this->currencyFactory->expects($this->once())
            ->method('create')
            ->willReturn($currentCurrency);

        $baseCurrency = $this->getBaseCurrencyMock($amount, $convertedAmount, $currentCurrency);
        $baseCurrency->expects($this->once())
            ->method('getRate')
            ->with($currentCurrency)
            ->willReturn(1.2);
        $store = $this->getStoreMock($baseCurrency);

        $this->assertEquals($convertedAmount, $this->priceCurrency->convert($amount, $store, $currency));
    }

    public function testConvertWithStoreCurrency()
    {
        $amount = 5.6;
        $currency = null;
        $convertedAmount = 9.3;

        $currentCurrency = $this->getCurrentCurrencyMock();
        $baseCurrency = $this->getBaseCurrencyMock($amount, $convertedAmount, $currentCurrency);
        $store = $this->getStoreMock($baseCurrency);
        $store->expects($this->atLeastOnce())
            ->method('getCurrentCurrency')
            ->willReturn($currentCurrency);

        $this->assertEquals($convertedAmount, $this->priceCurrency->convert($amount, $store, $currency));
    }

    public function testFormat()
    {
        $amount = 5.6;
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION;
        $includeContainer = false;
        $store = null;
        $formattedAmount = '5.6 grn';

        $currency = $this->getCurrentCurrencyMock();
        $currency->expects($this->once())
            ->method('formatPrecision')
            ->with($amount, $precision, [], $includeContainer)
            ->willReturn($formattedAmount);

        $this->assertEquals($formattedAmount, $this->priceCurrency->format(
            $amount,
            $includeContainer,
            $precision,
            $store,
            $currency
        ));
    }

    public function testConvertAndFormat()
    {
        $amount = 5.6;
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION;
        $includeContainer = false;
        $store = null;
        $convertedAmount = 9.3;
        $formattedAmount = '9.3 grn';

        $currency = $this->getCurrentCurrencyMock();
        $baseCurrency = $this->getBaseCurrencyMock($amount, $convertedAmount, $currency);
        $store = $this->getStoreMock($baseCurrency);

        $currency->expects($this->once())
            ->method('formatPrecision')
            ->with($convertedAmount, $precision, [], $includeContainer)
            ->willReturn($formattedAmount);

        $this->assertEquals($formattedAmount, $this->priceCurrency->convertAndFormat(
            $amount,
            $includeContainer,
            $precision,
            $store,
            $currency
        ));
    }

    public function testGetCurrencySymbol()
    {
        $storeId = 2;
        $currencySymbol = '$';

        $currencyMock = $this->getCurrentCurrencyMock();
        $currencyMock->expects($this->once())
            ->method('getCurrencySymbol')
            ->willReturn($currencySymbol);
        $this->assertEquals($currencySymbol, $this->priceCurrency->getCurrencySymbol($storeId, $currencyMock));
    }

    /**
     * @return MockObject
     */
    protected function getCurrentCurrencyMock()
    {
        $currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $currency;
    }

    /**
     * @param $baseCurrency
     * @return MockObject
     */
    protected function getStoreMock($baseCurrency)
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $store->expects($this->atLeastOnce())
            ->method('getBaseCurrency')
            ->willReturn($baseCurrency);

        return $store;
    }

    /**
     * @param $amount
     * @param $convertedAmount
     * @param $currency
     * @return MockObject
     */
    protected function getBaseCurrencyMock($amount, $convertedAmount, $currency)
    {
        $baseCurrency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();

        $baseCurrency->expects($this->once())
            ->method('convert')
            ->with($amount, $currency)
            ->willReturn($convertedAmount);

        return $baseCurrency;
    }

    public function testConvertAndRound()
    {
        $amount = 5.6;
        $storeCode = 2;
        $convertedAmount = 9.326;
        $roundedConvertedAmount = 9.33;

        $currency = $this->getCurrentCurrencyMock();
        $baseCurrency = $this->getBaseCurrencyMock($amount, $convertedAmount, $currency);
        $store = $this->getStoreMock($baseCurrency);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with($storeCode)
            ->willReturn($store);

        $this->assertEquals(
            $roundedConvertedAmount,
            $this->priceCurrency->convertAndRound($amount, $storeCode, $currency)
        );
    }
}
