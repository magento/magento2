<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_messages = array();

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
        $data = array();
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();
        @set_time_limit(0);
        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = array();
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
