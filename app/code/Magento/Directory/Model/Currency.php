<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Directory\Model\Currency\Filter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\Currency as LocaleCurrency;
use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;
use Magento\Framework\NumberFormatterFactory;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Currency model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Currency extends \Magento\Framework\Model\AbstractModel implements ResetAfterRequestInterface
{
    /**
     * CONFIG path constants
     */
    public const XML_PATH_CURRENCY_ALLOW = 'currency/options/allow';

    public const XML_PATH_CURRENCY_DEFAULT = 'currency/options/default';

    public const XML_PATH_CURRENCY_BASE = 'currency/options/base';

    /**
     * @var Filter
     */
    protected $_filter;

    /**
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
     * @var CurrencyConfig
     */
    private $currencyConfig;

    /**
     * @var LocalResolverInterface
     */
    private $localeResolver;

    /**
     * @var NumberFormatterFactory
     */
    private $numberFormatterFactory;

    /**
     * @var \Magento\Framework\NumberFormatter
     */
    private $numberFormatter;

    /**
     * @var array
     */
    private $numberFormatterCache;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param Currency\FilterFactory $currencyFilterFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param CurrencyConfig|null $currencyConfig
     * @param LocalResolverInterface|null $localeResolver
     * @param NumberFormatterFactory|null $numberFormatterFactory
     * @param Json|null $serializer
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
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?CurrencyConfig $currencyConfig = null,
        ?LocalResolverInterface $localeResolver = null,
        ?\Magento\Framework\NumberFormatterFactory $numberFormatterFactory = null,
        ?Json $serializer = null
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
        $this->currencyConfig = $currencyConfig ?: ObjectManager::getInstance()->get(CurrencyConfig::class);
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()->get(LocalResolverInterface::class);
        $this->numberFormatterFactory = $numberFormatterFactory ?:
            ObjectManager::getInstance()->get(NumberFormatterFactory::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Initializing Currency Resource model
     *
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
     * @param float $price
     * @param mixed $toCurrency
     * @return float
     * @throws LocalizedException
     */
    public function convert($price, $toCurrency = null)
    {
        if ($toCurrency === null) {
            return $price;
        } elseif ($rate = $this->getRate($toCurrency)) {
            return (float)$price * (float)$rate;
        }

        throw new LocalizedException(__(
            'Undefined rate from "%1-%2".',
            $this->getCode(),
            $this->getCurrencyCodeFromToCurrency($toCurrency)
        ));
    }

    /**
     * Return the currency code
     *
     * @param mixed $toCurrency
     *
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
     * Return formatted currency
     *
     * @param float $price
     * @param array $options
     *
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

        if ($this->canUseNumberFormatter($options)) {
            return $this->formatCurrency($price, $options);
        }

        return $this->_localeCurrency->getCurrency($this->getCode())->toCurrency($price, $options);
    }

    /**
     * Check if to use Intl.NumberFormatter to format currency.
     *
     * @param array $options
     * @return bool
     */
    private function canUseNumberFormatter(array $options): bool
    {
        $allowedOptions = [
            'precision',
            LocaleCurrency::CURRENCY_OPTION_DISPLAY,
            LocaleCurrency::CURRENCY_OPTION_SYMBOL
        ];

        if (!empty(array_diff(array_keys($options), $allowedOptions))) {
            return false;
        }

        if (array_key_exists('display', $options)
            && $options['display'] !== \Magento\Framework\Currency::NO_SYMBOL
            && $options['display'] !== \Magento\Framework\Currency::USE_SYMBOL
        ) {
            return false;
        }

        return true;
    }

    /**
     * Format currency.
     *
     * @param string $price
     * @param array $options
     * @return string
     */
    private function formatCurrency(string $price, array $options): string
    {
        $customerOptions = new \Magento\Framework\DataObject([]);

        $this->_eventManager->dispatch(
            'currency_display_options_forming',
            ['currency_options' => $customerOptions, 'base_code' => $this->getCode()]
        );
        $options += $customerOptions->toArray();

        $this->numberFormatter = $this->getNumberFormatter($options);

        $this->numberFormatter->setAttribute(
            \NumberFormatter::ROUNDING_MODE,
            \NumberFormatter::ROUND_HALFUP
        );

        $formattedCurrency = $this->numberFormatter->formatCurrency(
            $price,
            $this->getCode() ?? $this->numberFormatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE)
        );

        if ((array_key_exists(LocaleCurrency::CURRENCY_OPTION_DISPLAY, $options)
            && $options[LocaleCurrency::CURRENCY_OPTION_DISPLAY] === \Magento\Framework\Currency::NO_SYMBOL)) {
            $formattedCurrency = str_replace(' ', '', $formattedCurrency);
        }
        if (preg_match('/^(\x{200F})/u', $formattedCurrency, $match)) {
            $formattedCurrency = preg_replace('/^' . $match[1] . '/u', '', $formattedCurrency);
        }

        return preg_replace('/^\s+|\s+$/u', '', $formattedCurrency);
    }

    /**
     * Get NumberFormatter object from cache.
     *
     * @param array $options
     * @return \Magento\Framework\NumberFormatter
     */
    private function getNumberFormatter(array $options): \Magento\Framework\NumberFormatter
    {
        $locale = $this->localeResolver->getLocale() . ($this->getCode() ? '@currency=' . $this->getCode() : '');
        $key = 'currency_' . hash('sha256', $locale . $this->serializer->serialize($options));
        if (!isset($this->numberFormatterCache[$key])) {
            $this->numberFormatter = $this->numberFormatterFactory->create(
                ['locale' => $locale, 'style' => \NumberFormatter::CURRENCY]
            );

            $this->setOptions($options);
            $this->numberFormatterCache[$key] = $this->numberFormatter;
        }

        return $this->numberFormatterCache[$key];
    }

    /**
     * Set number formatter custom options.
     *
     * @param array $options
     * @return void
     */
    private function setOptions(array $options): void
    {
        if (array_key_exists(LocaleCurrency::CURRENCY_OPTION_SYMBOL, $options)) {
            $this->numberFormatter->setSymbol(
                \NumberFormatter::CURRENCY_SYMBOL,
                $options[LocaleCurrency::CURRENCY_OPTION_SYMBOL]
            );
        }
        if (array_key_exists(LocaleCurrency::CURRENCY_OPTION_DISPLAY, $options)
            && $options[LocaleCurrency::CURRENCY_OPTION_DISPLAY] === \Magento\Framework\Currency::NO_SYMBOL) {
            $this->numberFormatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, '');
        }
        if (array_key_exists('precision', $options)) {
            $this->numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $options['precision']);
        }
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
     * Return the price format to be displayed to user
     *
     * @return string
     */
    public function getOutputFormat()
    {
        $formatted = $this->formatTxt(0);
        $number = $this->formatTxt(0, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);
        return $formatted !== null ? str_replace($this->trimUnicodeDirectionMark($number), '%s', $formatted) : '';
    }

    /**
     * Retrieve allowed currencies according to config
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
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
    public function getConfigDefaultCurrencies()
    {
        return $this->currencyConfig->getConfigCurrencies(self::XML_PATH_CURRENCY_DEFAULT);
    }

    /**
     * Retrieve base config currency data by config path.
     *
     * @return array
     */
    public function getConfigBaseCurrencies()
    {
        return $this->currencyConfig->getConfigCurrencies(self::XML_PATH_CURRENCY_BASE);
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
            $string = preg_replace('/^' . $match[1] . '/u', '', $string);
        }
        return $string;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_rates = null;
    }
}
