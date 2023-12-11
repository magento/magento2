<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyInformationAcquirer;
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

        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            CurrencyInformationAcquirer::class,
            [
                'currencyInformationFactory' => $this->currencyInformationFactory,
                'exchangeRateFactory' => $this->exchangeRateFactory,
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
    }
}
