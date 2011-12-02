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
 * @category    Mage
 * @package     Mage_Directory
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract model for import currency
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Directory_Model_Currency_Import_Abstract
{
    /**
     * Retrieve currency codes
     *
     * @return array
     */
    protected function _getCurrencyCodes()
    {
        return Mage::getModel('Mage_Directory_Model_Currency')->getConfigAllowCurrencies();
    }

    /**
     * Retrieve default currency codes
     *
     * @return array
     */
    protected function _getDefaultCurrencyCodes()
    {
        return Mage::getModel('Mage_Directory_Model_Currency')->getConfigBaseCurrencies();
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
     * @return  Mage_Directory_Model_Currency_Import_Abstract
     */
    protected function _saveRates($rates)
    {
        foreach ($rates as $currencyCode => $currencyRates) {
            Mage::getModel('Mage_Directory_Model_Currency')
                ->setId($currencyCode)
                ->setRates($currencyRates)
                ->save();
        }
        return $this;
    }

    /**
     * Import rates
     *
     * @return Mage_Directory_Model_Currency_Import_Abstract
     */
    public function importRates()
    {
        $data = $this->fetchRates();
        $this->_saveRates($data);
        return $this;
    }

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
                }
                else {
                    $data[$currencyFrom][$currencyTo] = $this->_numberFormat($this->_convert($currencyFrom, $currencyTo));
                }
            }
            ksort($data[$currencyFrom]);
        }

        return $data;
    }

    protected function _numberFormat($number)
    {
        return $number;
    }

    public function getMessages()
    {
        return $this->_messages;
    }
}
