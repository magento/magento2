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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Currency model
 *
 * @category   Mage
 * @package    Mage_Directory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Directory_Model_Currency extends Mage_Core_Model_Abstract
{
    /**
     * CONFIG path constants
    */
    const XML_PATH_CURRENCY_ALLOW   = 'currency/options/allow';
    const XML_PATH_CURRENCY_DEFAULT = 'currency/options/default';
    const XML_PATH_CURRENCY_BASE    = 'currency/options/base';

    protected $_filter;

    /**
     * Currency Rates
     *
     * @var array
     */
    protected $_rates;


    protected function _construct()
    {
        $this->_init('Mage_Directory_Model_Resource_Currency');
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_getData('currency_code');
    }

    public function getCurrencyCode()
    {
        return $this->_getData('currency_code');
    }

    /**
     * Currency Rates getter
     *
     * @return array
     */
    public function getRates()
    {
        return $this->_rates;
    }

    /**
     * Currency Rates setter
     *
     * @param array Currency Rates
     * @return Mage_Directory_Model_Currency
     */
    public function setRates(array $rates)
    {
        $this->_rates = $rates;
        return $this;
    }

    /**
     * Loading currency data
     *
     * @param   string $id
     * @param   string $field
     * @return  Mage_Directory_Model_Currency
     */
    public function load($id, $field=null)
    {
        $this->unsRate();
        $this->setData('currency_code', $id);
        return $this;
    }

    /**
     * Get currency rate (only base=>allowed)
     *
     * @param   string $toCurrency
     * @return  double
     */
    public function getRate($toCurrency)
    {
        if (is_string($toCurrency)) {
            $code = $toCurrency;
        } elseif ($toCurrency instanceof Mage_Directory_Model_Currency) {
            $code = $toCurrency->getCurrencyCode();
        } else {
            throw Mage::exception('Mage_Directory', Mage::helper('Mage_Directory_Helper_Data')->__('Invalid target currency.'));
        }
        $rates = $this->getRates();
        if (!isset($rates[$code])) {
            $rates[$code] = $this->_getResource()->getRate($this->getCode(), $toCurrency);
            $this->setRates($rates);
        }
        return $rates[$code];
    }

    /**
     * Get currency rate (base=>allowed or allowed=>base)
     *
     * @param   string $toCurrency
     * @return  double
     */
    public function getAnyRate($toCurrency)
    {
        if (is_string($toCurrency)) {
            $code = $toCurrency;
        } elseif ($toCurrency instanceof Mage_Directory_Model_Currency) {
            $code = $toCurrency->getCurrencyCode();
        } else {
            throw Mage::exception('Mage_Directory', Mage::helper('Mage_Directory_Helper_Data')->__('Invalid target currency.'));
        }
        $rates = $this->getRates();
        if (!isset($rates[$code])) {
            $rates[$code] = $this->_getResource()->getAnyRate($this->getCode(), $toCurrency);
            $this->setRates($rates);
        }
        return $rates[$code];
    }

    /**
     * Convert price to currency format
     *
     * @param   double $price
     * @param   string $toCurrency
     * @return  double
     */
    public function convert($price, $toCurrency=null)
    {
        if (is_null($toCurrency)) {
            return $price;
        }
        elseif ($rate = $this->getRate($toCurrency)) {
            return $price*$rate;
        }

        throw new Exception(Mage::helper('Mage_Directory_Helper_Data')->__('Undefined rate from "%s-%s".', $this->getCode(), $toCurrency->getCode()));
    }

    /**
     * Get currency filter
     *
     * @return Mage_Directory_Model_Currency_Filter
     */
    public function getFilter()
    {
        if (!$this->_filter) {
            $this->_filter = new Mage_Directory_Model_Currency_Filter($this->getCode());
        }

        return $this->_filter;
    }

    /**
     * Format price to currency format
     *
     * @param   double $price
     * @param   bool $includeContainer
     * @return  string
     */
    public function format($price, $options=array(), $includeContainer = true, $addBrackets = false)
    {
        return $this->formatPrecision($price, 2, $options, $includeContainer, $addBrackets);
    }

    /**
     * Apply currency format to number with specific rounding precision
     *
     * @param   float $price
     * @param   int $precision
     * @param   array $options
     * @param   bool $includeContainer
     * @param   bool $addBrackets
     * @return  string
     */
    public function formatPrecision($price, $precision, $options=array(), $includeContainer = true, $addBrackets = false)
    {
        if (!isset($options['precision'])) {
            $options['precision'] = $precision;
        }
        if ($includeContainer) {
            return '<span class="price">' . ($addBrackets ? '[' : '') . $this->formatTxt($price, $options) . ($addBrackets ? ']' : '') . '</span>';
        }
        return $this->formatTxt($price, $options);
    }

    public function formatTxt($price, $options=array())
    {
        if (!is_numeric($price)) {
            $price = Mage::app()->getLocale()->getNumber($price);
        }
        /**
         * Fix problem with 12 000 000, 1 200 000
         *
         * %f - the argument is treated as a float, and presented as a floating-point number (locale aware).
         * %F - the argument is treated as a float, and presented as a floating-point number (non-locale aware).
         */
        $price = sprintf("%F", $price);
        return Mage::app()->getLocale()->currency($this->getCode())->toCurrency($price, $options);
    }

    public function getOutputFormat()
    {
        $formated = $this->formatTxt(0);
        $number = $this->formatTxt(0, array('display'=>Zend_Currency::NO_SYMBOL));
        return str_replace($number, '%s', $formated);
    }

    /**
     * Retrieve allowed currencies according to config
     *
     */
    public function getConfigAllowCurrencies()
    {
        $allowedCurrencies = $this->_getResource()->getConfigCurrencies($this, self::XML_PATH_CURRENCY_ALLOW);
        $appBaseCurrencyCode = Mage::app()->getBaseCurrencyCode();
        if (!in_array($appBaseCurrencyCode, $allowedCurrencies)) {
            $allowedCurrencies[] = $appBaseCurrencyCode;
        }
        foreach (Mage::app()->getStores() as $store) {
            $code = $store->getBaseCurrencyCode();
            if (!in_array($code, $allowedCurrencies)) {
                $allowedCurrencies[] = $code;
            }
        }

        return $allowedCurrencies;
    }

    /**
     * Retrieve default currencies according to config
     *
     */
    public function getConfigDefaultCurrencies()
    {
        $defaultCurrencies = $this->_getResource()->getConfigCurrencies($this, self::XML_PATH_CURRENCY_DEFAULT);
        return $defaultCurrencies;
    }


    public function getConfigBaseCurrencies()
    {
        $defaultCurrencies = $this->_getResource()->getConfigCurrencies($this, self::XML_PATH_CURRENCY_BASE);
        return $defaultCurrencies;
    }

    /**
     * Retrieve currency rates to other currencies
     *
     * @param string $currency
     * @param array $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies=null)
    {
        if ($currency instanceof Mage_Directory_Model_Currency) {
            $currency = $currency->getCode();
        }
        $data = $this->_getResource()->getCurrencyRates($currency, $toCurrencies);
        return $data;
    }

    /**
     * Save currency rates
     *
     * @param array $rates
     * @return object
     */
    public function saveRates($rates)
    {
        $this->_getResource()->saveRates($rates);
        return $this;
    }
}
