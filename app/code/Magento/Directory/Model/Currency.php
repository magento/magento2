<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Framework\Exception\InputException;
use Magento\Directory\Model\Currency\Filter;

/**
 * Currency model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Currency extends \Magento\Framework\Model\AbstractModel
{
    /**
     * CONFIG path constants
     */
    const XML_PATH_CURRENCY_ALLOW = 'currency/options/allow';

    const XML_PATH_CURRENCY_DEFAULT = 'currency/options/default';

    const XML_PATH_CURRENCY_BASE = 'currency/options/base';

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * Currency Rates
     *
     * @var array
     */
    protected $_rates;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \Magento\Directory\Model\Currency\FilterFactory
     */
    protected $_currencyFilterFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param Currency\FilterFactory $currencyFilterFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Directory\Model\Currency\FilterFactory $currencyFilterFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_localeFormat = $localeFormat;
        $this->_storeManager = $storeManager;
        $this->_directoryHelper = $directoryHelper;
        $this->_currencyFilterFactory = $currencyFilterFactory;
        $this->_localeCurrency = $localeCurrency;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Directory\Model\ResourceModel\Currency::class);
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

    /**
     * Get currency code
     *
     * @return string
     */
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
     * @param array $rates Currency Rates
     * @return $this
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
     * @return  $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($id, $field = null)
    {
        $this->unsRate();
        $this->setData('currency_code', $id);
        return $this;
    }

    /**
     * Get currency rate (only base => allowed)
     *
     * @param mixed $toCurrency
     * @return float
     */
    public function getRate($toCurrency)
    {
        $code = $this->getCurrencyCodeFromToCurrency($toCurrency);
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
     * @param mixed $toCurrency
     * @return float
     */
    public function getAnyRate($toCurrency)
    {
        $code = $this->getCurrencyCodeFromToCurrency($toCurrency);
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
     * @param   float $price
     * @param   mixed $toCurrency
     * @return  float
     * @throws \Exception
     */
    public function convert($price, $toCurrency = null)
    {
        if ($toCurrency === null) {
            return $price;
        } elseif ($rate = $this->getRate($toCurrency)) {
            return $price * $rate;
        }

        throw new \Exception(__(
            'Undefined rate from "%1-%2".',
            $this->getCode(),
            $this->getCurrencyCodeFromToCurrency($toCurrency)
        ));
    }

    /**
     * @param mixed $toCurrency
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    private function getCurrencyCodeFromToCurrency($toCurrency)
    {
        if (is_string($toCurrency)) {
            $code = $toCurrency;
        } elseif ($toCurrency instanceof \Magento\Directory\Model\Currency) {
            $code = $toCurrency->getCurrencyCode();
        } else {
            throw new InputException(__('Please correct the target currency.'));
        }
        return $code;
    }

    /**
     * Get currency filter
     *
     * @return Filter
     */
    public function getFilter()
    {
        if (!$this->_filter) {
            $this->_filter = $this->_currencyFilterFactory->create(['code' => $this->getCode()]);
        }

        return $this->_filter;
    }

    /**
     * Format price to currency format
     *
     * @param float $price
     * @param array $options
     * @param bool $includeContainer
     * @param bool $addBrackets
     * @return string
     */
    public function format($price, $options = [], $includeContainer = true, $addBrackets = false)
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
    public function formatPrecision(
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    ) {
        if (!isset($options['precision'])) {
            $options['precision'] = $precision;
        }
        if ($includeContainer) {
            return '<span class="price">' . ($addBrackets ? '[' : '') . $this->formatTxt(
                $price,
                $options
            ) . ($addBrackets ? ']' : '') . '</span>';
        }
        return $this->formatTxt($price, $options);
    }

    /**
     * @param float $price
     * @param array $options
     * @return string
     */
    public function formatTxt($price, $options = [])
    {
        if (!is_numeric($price)) {
            $price = $this->_localeFormat->getNumber($price);
        }
        /**
         * Fix problem with 12 000 000, 1 200 000
         *
         * %f - the argument is treated as a float, and presented as a floating-point number (locale aware).
         * %F - the argument is treated as a float, and presented as a floating-point number (non-locale aware).
         */
        $price = sprintf("%F", $price);
        return $this->_localeCurrency->getCurrency($this->getCode())->toCurrency($price, $options);
    }

    /**
     * Return currency symbol for current locale and currency code
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency($this->getCode())->getSymbol();
    }

    /**
     * @return string
     */
    public function getOutputFormat()
    {
        $formatted = $this->formatTxt(0);
        $number = $this->formatTxt(0, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);
        return str_replace($this->trimUnicodeDirectionMark($number), '%s', $formatted);
    }

    /**
     * Retrieve allowed currencies according to config
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
    {
        $allowedCurrencies = $this->_getResource()->getConfigCurrencies($this, self::XML_PATH_CURRENCY_ALLOW);
        $appBaseCurrencyCode = $this->_directoryHelper->getBaseCurrencyCode();
        if (!in_array($appBaseCurrencyCode, $allowedCurrencies)) {
            $allowedCurrencies[] = $appBaseCurrencyCode;
        }
        foreach ($this->_storeManager->getStores() as $store) {
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
     * @return array
     */
    public function getConfigDefaultCurrencies()
    {
        $defaultCurrencies = $this->_getResource()->getConfigCurrencies($this, self::XML_PATH_CURRENCY_DEFAULT);
        return $defaultCurrencies;
    }

    /**
     * @return array
     */
    public function getConfigBaseCurrencies()
    {
        $defaultCurrencies = $this->_getResource()->getConfigCurrencies($this, self::XML_PATH_CURRENCY_BASE);
        return $defaultCurrencies;
    }

    /**
     * Retrieve currency rates to other currencies
     *
     * @param string $currency
     * @param array|null $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        if ($currency instanceof \Magento\Directory\Model\Currency) {
            $currency = $currency->getCode();
        }
        $data = $this->_getResource()->getCurrencyRates($currency, $toCurrencies);
        return $data;
    }

    /**
     * Save currency rates
     *
     * @param array $rates
     * @return $this
     */
    public function saveRates($rates)
    {
        $this->_getResource()->saveRates($rates);
        return $this;
    }

    /**
     * This method removes LRM and RLM marks from string
     *
     * @param string $string
     * @return $this
     */
    private function trimUnicodeDirectionMark($string)
    {
        if (preg_match('/^(\x{200E}|\x{200F})/u', $string, $match)) {
            $string = preg_replace('/^'.$match[1].'/u', '', $string);
        }
        return $string;
    }
}
