<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

/**
 * Currency class
 *
 */
class CurrencyRepository implements \Magento\Directory\Api\CurrencyRepositoryInterface
{
    /**
     * @var \Magento\Directory\Model\Data\CurrencyInformationFactory
     */
    protected $currencyInformationFactory;

    /**
     * @var \Magento\Directory\Model\Data\ExchangeRateFactory
     */
    protected $exchangeRateFactory;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;
    
    /**
     * @param \Magento\Directory\Model\Data\CurrencyInformationFactory $currencyInformationFactory
     * @param \Magento\Directory\Model\Data\exchangeRateFactory $exchangeRateFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Directory\Model\Data\CurrencyInformationFactory $currencyInformationFactory,
        \Magento\Directory\Model\Data\exchangeRateFactory $exchangeRateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->currencyInformationFactory = $currencyInformationFactory;
        $this->exchangeRateFactory = $exchangeRateFactory;
        $this->storeManager = $storeManager;
    }
    /**
     * {@inheritdoc}
     */
    public function getCurrencyInfo()
    {
        $currency = $this->currencyInformationFactory->create();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        $currency->setBaseCurrencyCode($store->getBaseCurrencyCode());
        $currency->setDefaultDisplayCurrencyCode($store->getDefaultCurrencyCode());
        $currency->setAvailableCurrencyCodes($store->getAvailableCurrencyCodes(true));
        $exchangeRates = [];
        foreach ($store->getAvailableCurrencyCodes(true) as $currencyCode) {
            $exchangeRate = $this->exchangeRateFactory->create();
            $exchangeRate->setRate($store->getBaseCurrency()->getRate($currencyCode));
            $exchangeRate->setCurrencyTo($currencyCode);
            $exchangeRates[] = $exchangeRate;
        }

        $currency->setExchangeRates($exchangeRates);
        return $currency;
    }
}
