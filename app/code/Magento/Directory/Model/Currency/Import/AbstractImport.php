<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract model for import currency
 */
namespace Magento\Directory\Model\Currency\Import;

abstract class AbstractImport implements \Magento\Directory\Model\Currency\Import\ImportInterface
{
    /**
     * Messages
     *
     * @var array
     */
    protected $_messages = [];

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(\Magento\Directory\Model\CurrencyFactory $currencyFactory)
    {
        $this->_currencyFactory = $currencyFactory;
    }

    /**
     * Retrieve currency codes
     *
     * @return array
     */
    protected function _getCurrencyCodes()
    {
        return $this->_currencyFactory->create()->getConfigAllowCurrencies();
    }

    /**
     * Retrieve default currency codes
     *
     * @return array
     */
    protected function _getDefaultCurrencyCodes()
    {
        return $this->_currencyFactory->create()->getConfigBaseCurrencies();
    }

    /**
     * Retrieve rate
     *
     * @param   string $currencyFrom
     * @param   string $currencyTo
     * @return  float
     */
    abstract protected function _convert($currencyFrom, $currencyTo);

    /**
     * Saving currency rates
     *
     * @param   array $rates
     * @return  \Magento\Directory\Model\Currency\Import\AbstractImport
     */
    protected function _saveRates($rates)
    {
        foreach ($rates as $currencyCode => $currencyRates) {
            $this->_currencyFactory->create()->setId($currencyCode)->setRates($currencyRates)->save();
        }
        return $this;
    }

    /**
     * Import rates
     *
     * @return $this
     */
    public function importRates()
    {
        $data = $this->fetchRates();
        $this->_saveRates($data);
        return $this;
    }

    /**
     * @return array
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();
        set_time_limit(0);
        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = [];
            }

            foreach ($currencies as $currencyTo) {
                if ($currencyFrom == $currencyTo) {
                    $data[$currencyFrom][$currencyTo] = $this->_numberFormat(1);
                } else {
                    $data[$currencyFrom][$currencyTo] = $this->_numberFormat(
                        $this->_convert($currencyFrom, $currencyTo)
                    );
                }
            }
            ksort($data[$currencyFrom]);
        }
        ini_restore('max_execution_time');

        return $data;
    }

    /**
     * @param float|int $number
     * @return float|int
     */
    protected function _numberFormat($number)
    {
        return $number;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
