<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Exception;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Currency\FilterFactory;
use Magento\Directory\Model\ResourceModel\Currency as CurrencyResourceModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\InputException;
use Magento\Directory\Model\Currency\Filter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Currency_Exception;

/**
 * Currency model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Currency extends AbstractModel
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
     * @var FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Data
     */
    protected $_directoryHelper;

    /**
     * @var FilterFactory
     */
    protected $_currencyFilterFactory;

    /**
     * @var CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var CurrencyConfig
     */
    private $currencyConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormatInterface $localeFormat
     * @param StoreManagerInterface $storeManager
     * @param Data $directoryHelper
     * @param Currency\FilterFactory $currencyFilterFactory
     * @param CurrencyInterface $localeCurrency
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param CurrencyConfig $currencyConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormatInterface $localeFormat,
        StoreManagerInterface $storeManager,
        Data $directoryHelper,
        FilterFactory $currencyFilterFactory,
        CurrencyInterface $localeCurrency,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        CurrencyConfig $currencyConfig = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_localeFormat = $localeFormat;
        $this->_storeManager = $storeManager;
        $this->_directoryHelper = $directoryHelper;
        $this->_currencyFilterFactory = $currencyFilterFactory;
        $this->_localeCurrency = $localeCurrency;
        $this->currencyConfig = $currencyConfig ?: ObjectManager::getInstance()->get(CurrencyConfig::class);
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(CurrencyResourceModel::class);
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->_getData('currency_code');
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->_getData('currency_code');
    }

    /**
     * Currency Rates getter
     *
     * @return array
     */
    public function getRates(): array
    {
        return $this->_rates;
    }

    /**
     * Currency Rates setter
     *
     * @param array $rates Currency Rates
     * @return $this
     */
    public function setRates(array $rates): Currency
    {
        $this->_rates = $rates;
        return $this;
    }

    /**
     * Loading currency data
     *
     * @param string $currencyCode
     * @param string $field
     * @return  $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($currencyCode, $field = null): Currency
    {
        $this->unsRate();
        $this->setData('currency_code', $currencyCode);
        return $this;
    }

    /**
     * Get currency rate (only base => allowed)
     *
     * @param Currency|string $toCurrency
     * @return float
     */
    public function getRate($toCurrency): float
    {
        $code = $this->getCustomCurrencyCode($toCurrency);
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
     * @param Currency|string $toCurrency
     * @return float
     */
    public function getAnyRate($toCurrency)
    {
        $code = $this->getCustomCurrencyCode($toCurrency);
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
     * @param float $price
     * @param Currency|string|null $toCurrency
     * @return float
     * @throws Exception
     */
    public function convert($price, $toCurrency = null): float
    {
        if ($toCurrency === null) {
            return $price;
        } elseif ($rate = $this->getRate($toCurrency)) {
            return (float)bcmul($price, (string)$rate);
        }

        throw new Exception(__(
            'Undefined rate from "%1-%2".',
            $this->getCode(),
            $this->getCustomCurrencyCode($toCurrency)
        ));
    }

    /**
     * @param Currency|string $currency
     * @return string
     * @throws InputException
     */
    private function getCustomCurrencyCode($currency): string
    {
        if (is_string($currency)) {
            return $currency;
        } elseif ($currency instanceof Currency) {
            return $currency->getCurrencyCode();
        }

        throw new InputException(__('Please correct the target currency.'));
    }

    /**
     * Get currency filter
     *
     * @return Filter
     */
    public function getFilter(): Filter
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
    public function format($price, $options = [], $includeContainer = true, $addBrackets = false): string
    {
        return $this->formatPrecision($price, 2, $options, $includeContainer, $addBrackets);
    }

    /**
     * Apply currency format to number with specific rounding precision
     *
     * @param float $price
     * @param int $precision
     * @param array $options
     * @param bool $includeContainer
     * @param bool $addBrackets
     * @return  string
     */
    public function formatPrecision(
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    ): string {
        if (!isset($options['precision'])) {
            $options['precision'] = $precision;
        }

        if ($includeContainer) {
            return '<span class="price">' . ($addBrackets ? '[' : '')
                . $this->formatTxt($price, $options)
                . ($addBrackets ? ']' : '')
                . '</span>';
        }

        return $this->formatTxt($price, $options);
    }

    /**
     * @param float $price
     * @param array $options
     * @return string
     * @throws Zend_Currency_Exception
     */
    public function formatTxt($price, array $options = []): string
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
    public function getCurrencySymbol(): string
    {
        return $this->_localeCurrency->getCurrency($this->getCode())->getSymbol();
    }

    /**
     * @return string
     */
    public function getOutputFormat(): string
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
    public function getConfigAllowCurrencies(): array
    {
        $allowedCurrencies = $this->currencyConfig->getConfigCurrencies(self::XML_PATH_CURRENCY_ALLOW);
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
    public function getConfigDefaultCurrencies(): array
    {
        return $this->currencyConfig->getConfigCurrencies(self::XML_PATH_CURRENCY_DEFAULT);
    }

    /**
     * @return array
     */
    public function getConfigBaseCurrencies(): array
    {
        return $this->currencyConfig->getConfigCurrencies(self::XML_PATH_CURRENCY_BASE);
    }

    /**
     * Retrieve currency rates to other currencies
     *
     * @param string|array|Currency $currency
     * @param array|null $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null): array
    {
        if ($currency instanceof Currency) {
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
     * @throws LocalizedException
     */
    public function saveRates(array $rates): Currency
    {
        $this->_getResource()->saveRates($rates);
        return $this;
    }

    /**
     * This method removes LRM and RLM marks from string
     *
     * @param string $string
     * @return string
     */
    private function trimUnicodeDirectionMark(string $string): string
    {
        if (preg_match('/^(\x{200E}|\x{200F})/u', $string, $match)) {
            $string = preg_replace('/^' . $match[1] . '/u', '', $string);
        }

        return $string;
    }
}
