<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_I18n
 */

namespace Zend\I18n\View\Helper;

use Locale;
use NumberFormatter;
use Zend\View\Helper\AbstractHelper;

/**
 * View helper for formatting currency.
 *
 * @category   Zend
 * @package    Zend_I18n
 * @subpackage View
 */
class CurrencyFormat extends AbstractHelper
{
    /**
     * Locale to use instead of the default.
     *
     * @var string
     */
    protected $locale;

    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use.
     *
     * @var string
     */
    protected $currencyCode;

    /**
     * If set to true, the currency will be returned with two decimals
     *
     * @var bool
     */
    protected $showDecimals = true;

    /**
     * Formatter instances.
     *
     * @var array
     */
    protected $formatters = array();

    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use.
     *
     * @param  string $currencyCode
     * @return CurrencyFormat
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * Get the 3-letter ISO 4217 currency code indicating the currency to use.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Set if the view helper should show two decimals
     *
     * @param  bool $showDecimals
     * @return CurrencyFormat
     */
    public function setShouldShowDecimals($showDecimals)
    {
        $this->showDecimals = (bool) $showDecimals;
        return $this;
    }

    /**
     * Get if the view helper should show two decimals
     *
     * @return bool
     */
    public function shouldShowDecimals()
    {
        return $this->showDecimals;
    }

    /**
     * Set locale to use instead of the default.
     *
     * @param  string $locale
     * @return CurrencyFormat
     */
    public function setLocale($locale)
    {
        $this->locale = (string) $locale;
        return $this;
    }

    /**
     * Get the locale to use.
     *
     * @return string|null
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * Format a number.
     *
     * @param  float  $number
     * @param  string $currencyCode
     * @param  bool    $showDecimals
     * @param  string $locale
     * @return string
     */
    public function __invoke(
        $number,
        $currencyCode = null,
        $showDecimals = null,
        $locale       = null
    ) {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (null === $currencyCode) {
            $currencyCode = $this->getCurrencyCode();
        }
        if (null !== $showDecimals) {
            $this->setShouldShowDecimals($showDecimals);
        }

        $formatterId = md5($locale);

        if (!isset($this->formatters[$formatterId])) {
            $this->formatters[$formatterId] = new NumberFormatter(
                $locale,
                NumberFormatter::CURRENCY
            );
        }

        if ($this->shouldShowDecimals()) {
            $this->formatters[$formatterId]->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
        } else {
            $this->formatters[$formatterId]->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
        }

        return $this->formatters[$formatterId]->formatCurrency(
            $number, $currencyCode
        );
    }
}
