<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyInformationAcquirer;
use Magento\Directory\Model\Data\AvailableCurrency;
use Magento\Directory\Model\Data\AvailableCurrencyFactory;
use Magento\Directory\Model\Data\CurrencyInformation;
use Magento\Directory\Model\Data\CurrencyInformationFactory;
use Magento\Directory\Model\Data\ExchangeRate;
use Magento\Directory\Model\Data\ExchangeRateFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyInformationAcquirerTest extends TestCase
{
    /**
     * @var CurrencyInformationAcquirer
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $currencyInformationFactory;

    /**
     * @var MockObject
     */
    protected $exchangeRateFactory;

    /**
     * @var MockObject
     */
    protected $availableCurrencyFactory;

    /**
     * @var MockObject
     */
    protected $storeManager;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->currencyInformationFactory = $this->getMockBuilder(CurrencyInformationFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->exchangeRateFactory = $this->getMockBuilder(ExchangeRateFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->availableCurrencyFactory = $this->getMockBuilder(AvailableCurrencyFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            CurrencyInformationAcquirer::class,
            [
                'currencyInformationFactory' => $this->currencyInformationFactory,
                'exchangeRateFactory' => $this->exchangeRateFactory,
                'availableCurrencyFactory' => $this->availableCurrencyFactory,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * test GetCurrencyInfo
     */
    public function testGetCurrencyInfo()
    {
        /** @var ExchangeRate $exchangeRate */
        $exchangeRate = $this->getMockBuilder(ExchangeRate::class)
            ->addMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeRate->expects($this->any())->method('load')->willReturnSelf();
        $this->exchangeRateFactory->expects($this->any())->method('create')->willReturn($exchangeRate);

        /** @var  AvailableCurrency $availableCurrency */
        $availableCurrency = $this->getMockBuilder(AvailableCurrency::class)
            ->addMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $availableCurrency->expects($this->any())->method('load')->willReturnSelf();
        $this->availableCurrencyFactory->expects($this->any())->method('create')->willReturn($availableCurrency);

        /** @var CurrencyInformation $currencyInformation */
        $currencyInformation = $this->getMockBuilder(CurrencyInformation::class)
            ->addMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $currencyInformation->expects($this->any())->method('load')->willReturnSelf();
        $this->currencyInformationFactory->expects($this->any())->method('create')->willReturn($currencyInformation);

        /** @var Store $store */
        $store = $this->createMock(Store::class);

        /** @var Currency $baseCurrency */
        $baseCurrency = $this->createPartialMock(
            Currency::class,
            ['getCode', 'getCurrencySymbol', 'getRate']
        );
        $baseCurrency->expects($this->atLeastOnce())->method('getCode')->willReturn('USD');
        $baseCurrency->expects($this->atLeastOnce())->method('getCurrencySymbol')->willReturn('$');
        $baseCurrency->expects($this->once())->method('getRate')->with('AUD')->willReturn('0.80');

        $store->expects($this->atLeastOnce())->method('getBaseCurrency')->willReturn($baseCurrency);
        $store->expects($this->atLeastOnce())->method('getDefaultCurrency')->willReturn($baseCurrency);
        $store->expects($this->atLeastOnce())->method('getAvailableCurrencyCodes')->willReturn(['AUD']);

        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $result = $this->model->getCurrencyInfo();

        $this->assertEquals($currencyInformation, $result);
        $this->assertEquals('USD', $result->getBaseCurrencyCode());
        $this->assertEquals('$', $result->getBaseCurrencySymbol());
        $this->assertEquals('USD', $result->getDefaultDisplayCurrencyCode());
        $this->assertEquals('$', $result->getDefaultDisplayCurrencySymbol());
        $this->assertEquals(['AUD'], $result->getAvailableCurrencyCodes());
        $this->assertIsArray($result->getExchangeRates());
        $this->assertEquals([$exchangeRate], $result->getExchangeRates());
        $this->assertEquals('0.80', $result->getExchangeRates()[0]->getRate());
        $this->assertEquals('AUD', $result->getExchangeRates()[0]->getCurrencyTo());
        $this->assertIsArray($result->getAvailableCurrencies());
        $this->assertEquals([$availableCurrency], $result->getAvailableCurrencies());
        $this->assertEquals('AUD', $result->getAvailableCurrencies()[0]->getCode());
        $this->assertEquals('Australian Dollar', $result->getAvailableCurrencies()[0]->getName());
        $this->assertEquals('A$', $result->getAvailableCurrencies()[0]->getSymbol());
        $this->assertEquals('0', $result->getAvailableCurrencies()[0]->getValue());
    }
}
