<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Currency\Data;

use Locale;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\NumberFormatter;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Zend_Cache_Core;
use Magento\Framework\CurrencyInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Currency
{
    public const NO_SYMBOL = 1;
    public const USE_SYMBOL = 2;
    public const USE_SHORTNAME = 3;
    public const USE_NAME = 4;
    public const STANDARD = 8;
    public const RIGHT = 16;
    public const LEFT = 32;

    /**
     * @var Zend_Cache_Core
     */
    private static $cache = null;

    /**
     * @var array
     */
    protected $options = [
        'position' => self::STANDARD,
        'display' => self::NO_SYMBOL,
        'precision' => 2,
        'name' => null,
        'currency' => null,
        'symbol' => null,
        'locale' => null,
        'value' => 0
    ];

    /**
     * @var array
     */
    private $displayLocale = [
        'AED' => 'ar_AE', 'AFN' => 'fa_AF', 'ALL' => 'sq_AL', 'AMD' => 'hy_AM',
        'AOA' => 'pt_AO', 'ARS' => 'es_AR', 'AUD' => 'en_AU', 'AWG' => 'nl_AW',
        'AZN' => 'az_Latn_AZ', 'BAM' => 'bs_BA', 'BBD' => 'en_BB', 'BDT' => 'bn_BD',
        'BGN' => 'bg_BG', 'BHD' => 'ar_BH', 'BIF' => 'rn_BI', 'BMD' => 'en_BM',
        'BND' => 'ms_BN', 'BOB' => 'es_BO', 'BRL' => 'pt_BR', 'BSD' => 'en_BS',
        'BTN' => 'dz_BT', 'BWP' => 'en_BW', 'BYN' => 'be_BY', 'BZD' => 'en_BZ',
        'CAD' => 'en_CA', 'CDF' => 'sw_CD', 'CLP' => 'es_CL', 'COP' => 'es_CO',
        'CRC' => 'es_CR', 'CUP' => 'es_CU', 'CVE' => 'kea_CV', 'CZK' => 'cs_CZ',
        'DJF' => 'aa_DJ', 'DKK' => 'da_DK', 'DOP' => 'es_DO', 'DZD' => 'ar_DZ',
        'EGP' => 'ar_EG', 'ERN' => 'ti_ER', 'ETB' => 'en_ET', 'FJD' => 'hi_FJ',
        'FKP' => 'en_FK', 'GEL' => 'ka_GE', 'GHS' => 'ak_GH', 'GIP' => 'en_GI',
        'GMD' => 'en_GM', 'GNF' => 'fr_GN', 'GTQ' => 'es_GT', 'GYD' => 'en_GY',
        'HKD' => 'zh_Hant_HK', 'HNL' => 'es_HN', 'HRK' => 'hr_HR', 'HTG' => 'ht_HT',
        'HUF' => 'hu_HU', 'IDR' => 'id_ID', 'ILS' => 'he_IL', 'INR' => 'hi_IN',
        'IQD' => 'ar_IQ', 'IRR' => 'fa_IR', 'ISK' => 'is_IS', 'JMD' => 'en_JM',
        'JOD' => 'ar_JO', 'JPY' => 'ja_JP', 'KES' => 'en_KE', 'KGS' => 'ky_Cyrl_KG',
        'KHR' => 'km_KH', 'KMF' => 'ar_KM', 'KPW' => 'ko_KP', 'KRW' => 'ko_KR',
        'KWD' => 'ar_KW', 'KYD' => 'en_KY', 'KZT' => 'ru_KZ', 'LAK' => 'lo_LA',
        'LBP' => 'ar_LB', 'LKR' => 'si_LK', 'LRD' => 'en_LR', 'LYD' => 'ar_LY',
        'MAD' => 'ar_EH', 'MDL' => 'ro_MD', 'MGA' => 'mg_MG', 'MKD' => 'mk_MK',
        'MMK' => 'my_MM', 'MNT' => 'mn_Cyrl_MN', 'MOP' => 'zh_Hant_MO', 'MRU' => 'ar_MR',
        'MUR' => 'mfe_MU', 'MVR' => 'dv_MV', 'MWK' => 'ny_MW', 'MXN' => 'es_MX',
        'MYR' => 'ms_MY', 'MZN' => 'pt_MZ', 'NAD' => 'kj_NA', 'NGN' => 'en_NG',
        'NIO' => 'es_NI', 'NPR' => 'ne_NP', 'OMR' => 'ar_OM', 'PAB' => 'es_PA',
        'PEN' => 'es_PE', 'PGK' => 'tpi_PG', 'PHP' => 'fil_PH', 'PKR' => 'ur_PK',
        'PLN' => 'pl_PL', 'PYG' => 'gn_PY', 'QAR' => 'ar_QA', 'RON' => 'ro_RO',
        'RSD' => 'sr_Cyrl_RS', 'RUB' => 'ru_RU', 'RWF' => 'rw_RW', 'SAR' => 'ar_SA',
        'SBD' => 'en_SB', 'SCR' => 'crs_SC', 'SDG' => 'ar_SD', 'SEK' => 'sv_SE',
        'SGD' => 'en_SG', 'SHP' => 'en_SH', 'SLL' => 'kri_SL', 'SOS' => 'sw_SO',
        'SRD' => 'srn_SR', 'STN' => 'pt_ST', 'SYP' => 'ar_SY', 'SZL' => 'en_SZ',
        'THB' => 'th_TH', 'TJS' => 'tg_Cyrl_TJ', 'TMT' => 'tk_TM', 'TND' => 'ar_TN',
        'TOP' => 'to_TO', 'TRY' => 'tr_TR', 'TTD' => 'en_TT', 'TWD' => 'zh_Hant_TW',
        'TZS' => 'sw_TZ', 'UAH' => 'uk_UA', 'UGX' => 'sw_UG', 'USD' => 'en_GU',
        'UYU' => 'es_UY', 'UZS' => 'uz_Cyrl_UZ', 'VES' => 'es_VE', 'VND' => 'vi_VN',
        'VUV' => 'bi_VU', 'WST' => 'sm_WS', 'XCD' => 'en_AI', 'YER' => 'ar_YE',
        'ZAR' => 'en_ZA', 'ZMW' => 'en_ZM'
    ];

    /**
     * @param array|string|null $options
     * @param string|null $locale
     * @throws CurrencyException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct($options = null, $locale = null)
    {
        $callOptions = $options;

        if (is_array($options) && isset($options['display'])) {
            $this->options['display'] = $options['display'];
        }
        $this->setLocale($locale);

        if (!isset($this->options['currency']) || !is_array($options)) {
            $this->options['currency'] = $this->getShortName($options, $this->options['locale']);
        }
        if (!isset($this->options['name']) || !is_array($options)) {
            $this->options['name'] = $this->getName($options, $this->options['locale']);
        }
        if (!isset($this->options['symbol']) || !is_array($options)) {
            $this->options['symbol'] = $this->getSymbol($options, $this->options['locale']);
        }
        if ($this->options['currency'] === null && $this->options['name'] === null) {
            throw new CurrencyException(__(
                'Currency "%1" not found',
                $options
            ));
        }
        if ((is_array($callOptions) && !isset($callOptions['display']))
            || (!is_array($callOptions) && $this->options['display'] == self::NO_SYMBOL)) {
            if (!empty($this->options['symbol'])) {
                $this->options['display'] = self::USE_SYMBOL;
            } elseif (!empty($this->options['currency'])) {
                $this->options['display'] = self::USE_SHORTNAME;
            }
        }
    }

    /**
     * Returns a localized currency string.
     *
     * @param float|int|null $value
     * @param array $options
     * @return string
     * @throws CurrencyException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toCurrency($value = null, array $options = []): string
    {
        if ($value === null) {
            $value = $options['value'] ?? $this->options['value'];
        }
        if (is_array($value)) {
            $options += $value;
            if (isset($options['value'])) {
                $value = $options['value'];
            }
        }
        if (!isset($value) || !is_numeric($value)) {
            throw new CurrencyException(__(
                'Value "%1" has to be numeric',
                $value
            ));
        }
        if (isset($options['currency'])) {
            if (!isset($options['locale'])) {
                $options['locale'] = $this->options['locale'];
            }
            $options['currency'] = $this->getShortName($options['currency'], $options['locale']);
            $options['name'] = $this->getName($options['currency'], $options['locale']);
            $options['symbol'] = $this->getSymbol($options['currency'], $options['locale']);
        }
        $options = array_merge($this->options, $this->checkOptions($options));
        $numberFormatter = new NumberFormatter($options['locale'], NumberFormatter::CURRENCY);
        if (isset($options['precision'])) {
            $numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $options['precision']);
        }

        $value = $numberFormatter->format((float) $value);

        if (is_numeric($options['display']) === false) {
            $sign = $options['display'];
        } else {
            switch ($options['display']) {
                case self::USE_SYMBOL:
                    $sign = $options['symbol'];
                    break;
                case self::USE_SHORTNAME:
                    $sign = $options['currency'];
                    break;
                case self::USE_NAME:
                    $sign = $options['name'];
                    break;
                default:
                    $sign = '';
                    $value = str_replace(' ', '', $value);
            }
        }

        $currencySymbol = $this->getSymbol(null, $options['locale']);
        if ($options['position'] !== self::STANDARD) {
            $value = str_replace($currencySymbol, '', $value);
            $space = '';
            if (strpos($value, ' ') !== false) {
                $value = str_replace(' ', '', $value);
                $space = ' ';
            }

            if ($options['position'] == self::LEFT) {
                $value = $currencySymbol . $space . $value;
            } else {
                $value = $value . $space . $currencySymbol;
            }
        }

        return str_replace($currencySymbol, (string) $sign, $value);
    }

    /**
     * Sets the formatting options of the localized currency string.
     *
     * @param array $options
     * @return Currency
     * @throws CurrencyException
     */
    public function setFormat(array $options = []): Currency
    {
        $this->options = array_merge($this->options, $this->checkOptions($options));
        return $this;
    }

    /**
     * Internal function for checking static given locale parameter
     *
     * @param string|null $currency
     * @param string|null $locale
     * @return array
     * @throws CurrencyException
     */
    private function checkParams(?string $currency = null, ?string $locale = null): array
    {
        if (empty($locale) && !empty($currency)) {
            $locale = $currency;
            $currency = null;
        }
        if (strlen($locale) > 4) {
            $country = substr($locale, (strpos($locale, '_') + 1));
        } else {
            throw new CurrencyException(__(
                'No region found within the locale "%1"',
                $locale
            ));
        }

        $data = NumberFormatter::create($locale, NumberFormatter::CURRENCY)
            ->getTextAttribute(NumberFormatter::CURRENCY_CODE);

        if (!empty($currency) && !empty($data)) {
            $abbreviation = $currency;
        } else {
            $abbreviation = $data;
        }

        return ['locale' => $locale, 'currency' => $currency, 'name' => $abbreviation, 'country' => $country];
    }

    /**
     * Returns the actual or details of other currency symbols.
     *
     * @param string|null $currency
     * @param string|null $locale
     * @return string|null
     * @throws CurrencyException
     */
    public function getSymbol($currency = null, $locale = null): ?string
    {
        if ($currency === null && $locale === null) {
            return $this->options['symbol'];
        }
        $params = $this->checkParams($currency, $locale);

        if (!empty($params['currency'])) {
            $locale = $this->displayLocale[$params['currency']] ?? $params['locale'];
            $symbol = Currencies::getSymbol($params['currency'], $locale);
        } else {
            $symbol = Currencies::getSymbol($params['name'], $params['locale']);
        }

        return !empty($symbol) ? $symbol : null;
    }

    /**
     * Returns the actual or details of other currency shortnames.
     *
     * @param string|null $currency
     * @param string|null $locale
     * @return string|null
     * @throws CurrencyException
     */
    public function getShortName($currency = null, $locale = null): ?string
    {
        if ($currency === null && $locale === null) {
            return $this->options['currency'];
        }
        $params = $this->checkParams($currency, $locale);

        return $params['name'];
    }

    /**
     * Returns the actual or details of other currency names.
     *
     * @param string|null $currency
     * @param string|null $locale
     * @return string|null
     * @throws CurrencyException
     */
    public function getName($currency = null, $locale = null): ?string
    {
        if ($currency === null && $locale === null) {
            return $this->options['name'];
        }
        $params = $this->checkParams($currency, $locale);

        if (!empty($params['currency'])) {
            $name = Currencies::getName($params['currency'], $params['locale']);
        } else {
            $name = Currencies::getName($params['name'], $params['locale']);
        }

        if (empty($name)) {
            return null;
        }

        return $name;
    }

    /**
     * Returns a list of regions where this currency is or was known.
     *
     * @param string|null $currency
     * @return array
     * @throws CurrencyException
     */
    public function getRegionList($currency = null): array
    {
        if ($currency === null) {
            $currency = $this->options['currency'];
        }
        if (empty($currency)) {
            throw new CurrencyException(__('No currency defined'));
        }
        $countryCodes = Countries::getCountryCodes();
        $data = [];

        foreach ($countryCodes as $countryCode) {
            $locale = strtolower($countryCode) . '_' . $countryCode;
            $regionCurrency = NumberFormatter::create($locale, NumberFormatter::CURRENCY)
                ->getTextAttribute(NumberFormatter::CURRENCY_CODE);

            if ($regionCurrency === $currency) {
                $data[] = $countryCode;
            }
        }

        return $data;
    }

    /**
     * Returns a list of currencies which are used in this region.
     *
     * @param string|null $region
     * @return array
     */
    public function getCurrencyList($region = null): array
    {
        if (empty($region)) {
            if (strlen($this->options['locale']) > 4) {
                $region = substr($this->options['locale'], (strpos($this->options['locale'], '_') + 1));
            }
        }
        $locale = substr($this->options['locale'], 0, strpos($this->options['locale'], '_')) . '_' . $region;

        $data = NumberFormatter::create($locale, NumberFormatter::CURRENCY)
            ->getTextAttribute(NumberFormatter::CURRENCY_CODE);

        return explode(' ', $data);
    }

    /**
     * Returns the actual currency name.
     *
     * @return string
     * @throws CurrencyException
     */
    public function toString(): string
    {
        return $this->toCurrency();
    }

    /**
     * Returns the currency name.
     *
     * @return string
     * @throws CurrencyException
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Returns the set cache.
     *
     * @return Zend_Cache_Core
     */
    public static function getCache()
    {
        return self::$cache;
    }

    /**
     * Sets a cache for Currency
     *
     * @param Zend_Cache_Core $cache
     * @return void
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        self::$cache = $cache;
    }

    /**
     * Returns true when a cache is set.
     *
     * @return bool
     */
    public static function hasCache()
    {
        return self::$cache !== null;
    }

    /**
     * Removes any set cache.
     *
     * @return void
     */
    public static function removeCache()
    {
        self::$cache = null;
    }

    /**
     * Clears all set cache data.
     *
     * @param string|null $tag
     * @return void
     * @throws \Zend_Cache_Exception
     */
    public static function clearCache($tag = null): void
    {
        if ($tag) {
            self::$cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tag);
        } else {
            self::$cache->clean(\Zend_Cache::CLEANING_MODE_ALL);
        }
    }

    /**
     * Sets a new locale for data retirement.
     *
     * @param string|null $locale
     * @return Currency
     * @throws CurrencyException
     */
    public function setLocale($locale = null): Currency
    {
        $locale = $locale ?? Locale::getDefault();

        if (strlen($locale) > 4) {
            $this->options['locale'] = $locale;
        } else {
            throw new CurrencyException(__(
                'No region found within the locale "%1"',
                $locale
            ));
        }

        $this->options['currency'] = $this->getShortName(null, $this->options['locale']);
        $this->options['name'] = $this->getName(null, $this->options['locale']);
        $this->options['symbol'] = $this->getSymbol(null, $this->options['locale']);

        return $this;
    }

    /**
     * Returns the actual set locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->options['locale'];
    }

    /**
     * Returns the value.
     *
     * @return float
     */
    public function getValue(): float
    {
        return $this->options['value'];
    }

    /**
     * Adds a currency.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return Currency
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function setValue($value, $currency = null): Currency
    {
        $this->options['value'] = $this->exchangeCurrency($value, $currency);
        return $this;
    }

    /**
     * Adds a currency.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return Currency
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function add($value, $currency = null): Currency
    {
        $value = $this->exchangeCurrency($value, $currency);
        $this->options['value'] += (float) $value;

        return $this;
    }

    /**
     * Sub a currency.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return Currency
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function sub($value, $currency = null): Currency
    {
        $value = $this->exchangeCurrency($value, $currency);
        $this->options['value'] -= (float) $value;

        return $this;
    }

    /**
     * Divides a currency.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return Currency
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function div($value, $currency = null): Currency
    {
        $value = $this->exchangeCurrency($value, $currency);
        $this->options['value'] /= (float) $value;

        return $this;
    }

    /**
     * Multiplies a currency.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return Currency
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function mul($value, $currency = null): Currency
    {
        $value = $this->exchangeCurrency($value, $currency);
        $this->options['value'] *= (float) $value;

        return $this;
    }

    /**
     * Calculates the modulo from a currency.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return Currency
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function mod($value, $currency = null): Currency
    {
        $value = $this->exchangeCurrency($value, $currency);
        $this->options['value'] %= (float) $value;

        return $this;
    }

    /**
     * Compares two currencies.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return int
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function compare($value, $currency = null): int
    {
        $value = $this->exchangeCurrency($value, $currency);
        $value = $this->options['value'] - $value;
        return $value <=> 0;
    }

    /**
     * Returns true when the two currencies are equal.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return bool
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function equals($value, $currency = null): bool
    {
        $value = $this->exchangeCurrency($value, $currency);
        return $this->options['value'] == $value;
    }

    /**
     * Returns true when the currency is more than the given value.
     *
     * @param float|int|Currency $value
     * @param string|Currency $currency
     * @return bool
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function isMore($value, $currency = null): bool
    {
        $value = $this->exchangeCurrency($value, $currency);
        return $this->options['value'] > $value;
    }

    /**
     * Returns true when the currency is less than the given value.
     *
     * @param float|int|CurrencyInterface $value
     * @param string|CurrencyInterface $currency
     * @return bool
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function isLess($value, $currency = null): bool
    {
        $value = $this->exchangeCurrency($value, $currency);
        return $this->options['value'] < $value;
    }

    /**
     * Internal method which calculates the exchange currency.
     *
     * @param float|int|CurrencyInterface $value
     * @param string|CurrencyInterface $currency
     * @return int
     * @throws CurrencyException
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    protected function exchangeCurrency($value, $currency)
    {
        if ($value instanceof CurrencyInterface) {
            $currency = $value->getShortName();
            $value = $value->getValue();
        } else {
            $currency = $this->getShortName($currency, $this->getLocale());
        }
        $rate = 1;

        if ($currency !== $this->getShortName()) {
            // This exception throw because Zend_Service no longer exists.
            throw new CurrencyException(__('No exchange service not work'));
        }

        return $value * $rate;
    }

    /**
     * Internal method for checking the options array.
     *
     * @param array $options Options to check
     * @return array
     * @throws CurrencyException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function checkOptions($options = []): array
    {
        if (count($options) === 0) {
            return $this->options;
        }

        foreach ($options as $name => $value) {
            $name = strtolower($name);

            if ($name !== 'format') {
                if (gettype($value) === 'string') {
                    $value = strtolower($value);
                }
            }

            switch ($name) {
                case 'position':
                    if (!in_array($value, [self::STANDARD, self::RIGHT, self::LEFT], true)) {
                        throw new CurrencyException(
                            __('Unknown position "%1"', $value)
                        );
                    }
                    break;
                case 'display':
                    if (is_numeric($value)
                        && !in_array(
                            $value,
                            [self::NO_SYMBOL, self::USE_SYMBOL, self::USE_SHORTNAME, self::USE_NAME],
                            true
                        )
                    ) {
                        throw new CurrencyException(
                            __('Unknown display "%1"', $value)
                        );
                    }
                    break;
                case 'precision':
                    if ($value === null) {
                        $value = -1;
                    }
                    if ($value < -1 || $value > 30) {
                        throw new CurrencyException(__(
                            '"%1" precision has to be between -1 and 30.',
                            $value
                        ));
                    }
                    break;
            }
        }

        return $options;
    }
}
