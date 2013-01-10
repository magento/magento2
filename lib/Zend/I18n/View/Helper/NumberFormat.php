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
 * View helper for formatting dates.
 *
 * @category   Zend
 * @package    Zend_I18n
 * @subpackage View
 */
class NumberFormat extends AbstractHelper
{
    /**
     * Locale to use instead of the default.
     *
     * @var string
     */
    protected $locale;

    /**
     * NumberFormat style to use.
     *
     * @var integer
     */
    protected $formatStyle;

    /**
     * NumberFormat type to use.
     *
     * @var integer
     */
    protected $formatType;

    /**
     * Formatter instances.
     *
     * @var array
     */
    protected $formatters = array();

    /**
     * Set format style to use instead of the default.
     *
     * @param  integer $formatStyle
     * @return NumberFormat
     */
    public function setFormatStyle($formatStyle)
    {
        $this->formatStyle = (int) $formatStyle;
        return $this;
    }

    /**
     * Get the format style to use.
     *
     * @return integer
     */
    public function getFormatStyle()
    {
        if (null === $this->formatStyle) {
            $this->formatStyle = NumberFormatter::DECIMAL;
        }
        return $this->formatStyle;
    }

    /**
     * Set format type to use instead of the default.
     *
     * @param  integer $formatType
     * @return NumberFormat
     */
    public function setFormatType($formatType)
    {
        $this->formatType = (int) $formatType;
        return $this;
    }

    /**
     * Get the format type to use.
     *
     * @return integer
     */
    public function getFormatType()
    {
        if (null === $this->formatType) {
            $this->formatType = NumberFormatter::TYPE_DEFAULT;
        }
        return $this->formatType;
    }

    /**
     * Set locale to use instead of the default.
     *
     * @param string $locale
     * @return NumberFormat
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
     * @param  integer|float $number
     * @param  integer       $formatStyle
     * @param  integer       $formatType
     * @param  string        $locale
     * @return string
     */
    public function __invoke(
        $number,
        $formatStyle = null,
        $formatType  = null,
        $locale      = null
    ) {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (null === $formatStyle) {
            $formatStyle = $this->getFormatStyle();
        }
        if (null === $formatType) {
            $formatType = $this->getFormatType();
        }

        $formatterId = md5($formatStyle . "\0" . $locale);

        if (!isset($this->formatters[$formatterId])) {
            $this->formatters[$formatterId] = new NumberFormatter(
                $locale,
                $formatStyle
            );
        }

        return $this->formatters[$formatterId]->format($number, $formatType);
    }
}
