<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\CurrencyInformationAcquirer;

/**
 * Class CurrencyInformationAcquirerTest
 */
class CurrencyInformationAcquirerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\CurrencyInformationAcquirer
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyInformationFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $exchangeRateFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = \Magento\Directory\Model\Data\CurrencyInformationFactory::class;
        $this->currencyInformationFactory = $this->getMock($className, ['create'], [], '', false);

        $className = \Magento\Directory\Model\Data\ExchangeRateFactory::class;
        $this->exchangeRateFactory = $this->getMock($className, ['create'], [], '', false);

        $className = \Magento\Store\Model\StoreManager::class;
        $this->storeManager = $this->getMock($className, ['getStore'], [], '', false);

        $this->model = $this->objectManager->getObject(
            \Magento\Directory\Model\CurrencyInformationAcquirer::class,
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
        /** @var \Magento\Directory\Model\Data\ExchangeRate $exchangeRate */
        $exchangeRate = $this->getMock(\Magento\Directory\Model\Data\ExchangeRate::class, ['load'], [], '', false);

        $exchangeRate->expects($this->any())->method('load')->willReturnSelf();
        $this->exchangeRateFactory->expects($this->any())->method('create')->willReturn($exchangeRate);

        /** @var \Magento\Directory\Model\Data\CurrencyInformation $currencyInformation */
        $currencyInformation = $this->getMock(
            \Magento\Directory\Model\Data\CurrencyInformation::class,
            ['load'],
            [],
            '',
            false
        );

        $currencyInformation->expects($this->any())->method('load')->willReturnSelf();
        $this->currencyInformationFactory->expects($this->any())->method('create')->willReturn($currencyInformation);

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);

        /** @var \Magento\Directory\Model\Currency $baseCurrency */
        $baseCurrency = $this->getMock(
            \Magento\Directory\Model\Currency::class,
            ['getCode', 'getCurrencySymbol', 'getRate'],
            [],
            '',
            false
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
        $this->assertTrue(is_array($result->getExchangeRates()));
        $this->assertEquals([$exchangeRate], $result->getExchangeRates());
        $this->assertEquals('0.80', $result->getExchangeRates()[0]->getRate());
        $this->assertEquals('AUD', $result->getExchangeRates()[0]->getCurrencyTo());
    }
}
