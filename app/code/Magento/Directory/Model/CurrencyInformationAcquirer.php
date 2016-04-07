<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

/**
 * Currency information acquirer class
 */
class CurrencyInformationAcquirer implements \Magento\Directory\Api\CurrencyInformationAcquirerInterface
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
     * @param \Magento\Directory\Model\Data\ExchangeRateFactory $exchangeRateFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Directory\Model\Data\CurrencyInformationFactory $currencyInformationFactory,
        \Magento\Directory\Model\Data\ExchangeRateFactory $exchangeRateFactory,
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
        $currencyInfo = $this->currencyInformationFactory->create();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $currencyInfo->setBaseCurrencyCode($store->getBaseCurrency()->getCode());
        $currencyInfo->setBaseCurrencySymbol($store->getBaseCurrency()->getCurrencySymbol());

        $currencyInfo->setDefaultDisplayCurrencyCode($store->getDefaultCurrency()->getCode());
        $currencyInfo->setDefaultDisplayCurrencySymbol($store->getDefaultCurrency()->getCurrencySymbol());

        $currencyInfo->setAvailableCurrencyCodes($store->getAvailableCurrencyCodes(true));

        $exchangeRates = [];
        foreach ($store->getAvailableCurrencyCodes(true) as $currencyCode) {
            $exchangeRate = $this->exchangeRateFactory->create();
            $exchangeRate->setRate($store->getBaseCurrency()->getRate($currencyCode));
            $exchangeRate->setCurrencyTo($currencyCode);
            $exchangeRates[] = $exchangeRate;
        }
        $currencyInfo->setExchangeRates($exchangeRates);

        return $currencyInfo;
    }
}
