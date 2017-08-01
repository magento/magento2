<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

/**
 * Currency information acquirer class
 * @since 2.0.0
 */
class CurrencyInformationAcquirer implements \Magento\Directory\Api\CurrencyInformationAcquirerInterface
{
    /**
     * @var \Magento\Directory\Model\Data\CurrencyInformationFactory
     * @since 2.0.0
     */
    protected $currencyInformationFactory;

    /**
     * @var \Magento\Directory\Model\Data\ExchangeRateFactory
     * @since 2.0.0
     */
    protected $exchangeRateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;
    
    /**
     * @param \Magento\Directory\Model\Data\CurrencyInformationFactory $currencyInformationFactory
     * @param \Magento\Directory\Model\Data\ExchangeRateFactory $exchangeRateFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
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
     * @since 2.0.0
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
