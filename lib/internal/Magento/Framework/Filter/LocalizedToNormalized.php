<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Exception;
use IntlDateFormatter;
use Laminas\Filter\FilterInterface;
use Laminas\I18n\Filter\NumberParse;
use NumberFormatter;

class LocalizedToNormalized implements FilterInterface
{
    /**
     * @var array
     */
    protected $_options = [
        'locale'      => null,
        'date_format' => null,
        'precision'   => null
    ];

    /**
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Returns the set options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets options to use
     *
     * @param array|null $options
     * @return LocalizedToNormalized
     */
    public function setOptions(array $options = null)
    {
        $this->_options = $options + $this->_options;

        return $this;
    }

    /**
     * Defined by FilterInterface
     *
     * Normalizes the given input
     *
     * @param  string $value Value to normalized
     * @return string|array The normalized value
     */
    public function filter($value)
    {
        if (is_numeric($value)) {
            $numberParse = new NumberParse($this->_options['locale'], NumberFormatter::PATTERN_DECIMAL);
            return (string) $numberParse->filter($value);
        } elseif ($this->_options['date_format'] === null && strpos($value, ':') !== false) {
            $formatter = new IntlDateFormatter(
                $this->_options['locale'],
                IntlDateFormatter::SHORT,
                IntlDateFormatter::SHORT
            );
            $formatter->setPattern($this->_options['date_format']);
            return $formatter->format(strtotime($value));
        } elseif ($this->checkDateFormat($value)) {
            return $this->parseDate($value);
        }

        return $value;
    }

    /**
     * Returns if the given datestring contains all date parts from the given format.
     *
     * @param string $date
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkDateFormat(string $date): bool
    {
        try {
            $date = $this->parseDate($date);
        } catch (Exception $e) {
            return false;
        }
        $options = $this->_options;

        if (iconv_strpos($options['date_format'], 'd', 0, 'UTF-8') !== false
            && (!isset($date['day']) || ($date['day'] === ""))
        ) {
            return false;
        }
        if (iconv_strpos($options['date_format'], 'M', 0, 'UTF-8') !== false
            && (!isset($date['month']) || ($date['month'] === ""))
        ) {
            return false;
        }
        if ((iconv_strpos($options['date_format'], 'Y', 0, 'UTF-8') !== false
                || iconv_strpos($options['date_format'], 'y', 0, 'UTF-8') !== false)
            && (!isset($date['year']) || ($date['year'] === ""))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Parse date and split in named array fields.
     *
     * @param string $date
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function parseDate($date)
    {
        $format = $this->_options['date_format'];
        $result['date_format'] = $format;
        $result['locale'] = $this->_options['locale'];
        $day = iconv_strpos($format, 'd') !== false ? iconv_strpos($format, 'd') : iconv_strpos($format, 'D');
        $month = iconv_strpos($format, 'M');
        $year = iconv_strpos($format, 'y') !== false ? iconv_strpos($format, 'y') : iconv_strpos($format, 'Y');
        $parse = [];

        if ($day !== false) {
            $parse[$day] = 'd';
        }
        if ($month !== false) {
            $parse[$month] = 'M';
        }
        if ($year !== false) {
            $parse[$year]  = 'y';
        }
        preg_match_all('/\d+/u', $date, $splitted);
        $split = false;

        if (count($splitted[0]) == 1) {
            $split = 0;
        }
        ksort($parse);
        $cnt = 0;

        foreach ($parse as $value) {
            if ($value === 'd') {
                if ($split === false) {
                    if (count($splitted[0]) > $cnt) {
                        $result['day'] = $splitted[0][$cnt];
                    }
                } else {
                    $result['day'] = iconv_substr($splitted[0][0], $split, 2);
                    $split += 2;
                }
            }
            if ($value === 'M') {
                if ($split === false) {
                    if (count($splitted[0]) > $cnt) {
                        $result['month'] = $splitted[0][$cnt];
                    }
                } else {
                    $result['month'] = iconv_substr($splitted[0][0], $split, 2);
                    $split += 2;
                }
            }
            if ($value === 'y') {
                $length = 2;
                if ((iconv_substr($format, $year, 4) == 'yyyy')
                    || (iconv_substr($format, $year, 4) == 'YYYY')) {
                    $length = 4;
                }
                if ($split === false) {
                    if (count($splitted[0]) > $cnt) {
                        $result['year']   = $splitted[0][$cnt];
                    }
                } else {
                    $result['year'] = iconv_substr($splitted[0][0], $split, $length);
                    $split += $length;
                }
            }
            ++$cnt;
        }

        return $result;
    }
}
