<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract model for import currency
 */
namespace Magento\Directory\Model\Currency\Import;

/**
 * @api
 * @since 2.0.0
 */
abstract class AbstractImport implements \Magento\Directory\Model\Currency\Import\ImportInterface
{
    /**
     * Messages
     *
     * @var array
     * @since 2.0.0
     */
    protected $_messages = [];

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     * @since 2.0.0
     */
    protected $_currencyFactory;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @since 2.0.0
     */
    public function __construct(\Magento\Directory\Model\CurrencyFactory $currencyFactory)
    {
        $this->_currencyFactory = $currencyFactory;
    }

    /**
     * Retrieve currency codes
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getCurrencyCodes()
    {
        return $this->_currencyFactory->create()->getConfigAllowCurrencies();
    }

    /**
     * Retrieve default currency codes
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract protected function _convert($currencyFrom, $currencyTo);

    /**
     * Saving currency rates
     *
     * @param   array $rates
     * @return  \Magento\Directory\Model\Currency\Import\AbstractImport
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function importRates()
    {
        $data = $this->fetchRates();
        $this->_saveRates($data);
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _numberFormat($number)
    {
        return $number;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
