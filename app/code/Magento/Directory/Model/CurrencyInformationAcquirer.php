<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Magento\Directory\Model\Data\AvailableCurrencyFactory
     */
    protected $availableCurrencyFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @param \Magento\Directory\Model\Data\CurrencyInformationFactory $currencyInformationFactory
     * @param \Magento\Directory\Model\Data\ExchangeRateFactory $exchangeRateFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Directory\Model\Data\AvailableCurrencyFactory $availableCurrencyFactory
     */
    public function __construct(
        \Magento\Directory\Model\Data\CurrencyInformationFactory $currencyInformationFactory,
        \Magento\Directory\Model\Data\ExchangeRateFactory $exchangeRateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Directory\Model\Data\AvailableCurrencyFactory $availableCurrencyFactory
    ) {
        $this->currencyInformationFactory = $currencyInformationFactory;
        $this->exchangeRateFactory = $exchangeRateFactory;
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->availableCurrencyFactory = $availableCurrencyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyInfo()
    {
        /** @var \Magento\Directory\Model\Data\CurrencyInformation $currencyInfo */
        $currencyInfo = $this->currencyInformationFactory->create();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $currencyInfo->setBaseCurrencyCode($store->getBaseCurrency()->getCode());
        $currencyInfo->setBaseCurrencySymbol($store->getBaseCurrency()->getCurrencySymbol());

        $currencyInfo->setDefaultDisplayCurrencyCode($store->getDefaultCurrency()->getCode());
        $currencyInfo->setDefaultDisplayCurrencySymbol($store->getDefaultCurrency()->getCurrencySymbol());

        $currencyInfo->setAvailableCurrencyCodes($store->getAvailableCurrencyCodes(true));

        $exchangeRates = [];
        $availableCurrencies = [];
        foreach ($store->getAvailableCurrencyCodes(true) as $currencyCode) {
            $currency = $this->localeCurrency->getCurrency($currencyCode);

            if ($currency instanceof \Magento\Framework\Currency) {
                /** @var \Magento\Directory\Model\Data\AvailableCurrency $availableCurrency */
                $availableCurrency = $this->availableCurrencyFactory->create();
                $availableCurrency->setSymbol($currency->getSymbol());
                $availableCurrency->setName($currency->getName());
                $availableCurrency->setValue($currency->getValue());
                $availableCurrency->setCode($currencyCode);
                $availableCurrencies[] = $availableCurrency;
            }

            /** @var \Magento\Directory\Model\Data\ExchangeRate $exchangeRate */
            $exchangeRate = $this->exchangeRateFactory->create();
            $exchangeRate->setRate($store->getBaseCurrency()->getRate($currencyCode));
            $exchangeRate->setCurrencyTo($currencyCode);
            $exchangeRates[] = $exchangeRate;
        }
        $currencyInfo->setExchangeRates($exchangeRates);
        $currencyInfo->setAvailableCurrencies($availableCurrencies);

        return $currencyInfo;
    }
}
