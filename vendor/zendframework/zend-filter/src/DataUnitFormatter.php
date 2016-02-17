<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Zend\Filter\Exception\InvalidArgumentException;

final class DataUnitFormatter extends AbstractFilter
{
    const MODE_BINARY = 'binary';
    const MODE_DECIMAL = 'decimal';

    const BASE_BINARY = 1024;
    const BASE_DECIMAL = 1000;

    /**
     * A list of all possible filter modes:
     *
     * @var array
     */
    private static $modes = array(
        self::MODE_BINARY,
        self::MODE_DECIMAL,
    );

    /**
     * A list of standardized binary prefix formats for decimal and binary mode
     * @link https://en.wikipedia.org/wiki/Binary_prefix
     *
     * @var array
     */
    private static $standardizedPrefixes = array(
        // binary IEC units:
        self::MODE_BINARY => array('', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi'),
        // decimal SI units:
        self::MODE_DECIMAL => array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'),
    );

    /**
     * Default options:
     *
     * @var array
     */
    protected $options = array(
        'mode'         => self::MODE_DECIMAL,
        'unit'         => '',
        'precision'    => 2,
        'prefixes'     => array(),
    );

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (!static::isOptions($options)) {
            throw new InvalidArgumentException('The unit filter needs options to work.');
        }

        if (!isset($options['unit'])) {
            throw new InvalidArgumentException('The unit filter needs a unit to work with.');
        }

        $this->setOptions($options);
    }

    /**
     * Define the mode of the filter. Possible values can be fount at self::$modes.
     *
     * @param string $mode
     *
     * @throws InvalidArgumentException
     */
    protected function setMode($mode)
    {
        $mode = strtolower($mode);
        if (!in_array($mode, self::$modes)) {
            throw new InvalidArgumentException(sprintf('Invalid binary mode: %s', $mode));
        }
        $this->options['mode'] = $mode;
    }

    /**
     * Get current filter mode
     *
     * @return string
     */
    protected function getMode()
    {
        return $this->options['mode'];
    }

    /**
     * Find out if the filter is in decimal mode.
     *
     * @return bool
     */
    protected function isDecimalMode()
    {
        return $this->getMode() == self::MODE_DECIMAL;
    }

    /**
     * Find out if the filter is in binary mode.
     *
     * @return bool
     */
    protected function isBinaryMode()
    {
        return $this->getMode() == self::MODE_BINARY;
    }

    /**
     * Define the unit of the filter. Possible values can be fount at self::$types.
     *
     * @param string $unit
     */
    protected function setUnit($unit)
    {
        $this->options['unit'] = (string) $unit;
    }

    /**
     * Get current filter type
     *
     * @return string
     */
    protected function getUnit()
    {
        return $this->options['unit'];
    }

    /**
     * Set the precision of the filtered result.
     *
     * @param $precision
     */
    protected function setPrecision($precision)
    {
        $this->options['precision'] = (int) $precision;
    }

    /**
     * Get the precision of the filtered result.
     *
     * @return int
     */
    protected function getPrecision()
    {
        return $this->options['precision'];
    }

    /**
     * Set the precision of the result.
     *
     * @param array $prefixes
     */
    protected function setPrefixes(array $prefixes)
    {
        $this->options['prefixes'] = $prefixes;
    }

    /**
     * Get the predefined prefixes or use the build-in standardized lists of prefixes.
     *
     * @return array
     */
    protected function getPrefixes()
    {
        $prefixes = $this->options['prefixes'];
        if ($prefixes) {
            return $prefixes;
        }

        return self::$standardizedPrefixes[$this->getMode()];
    }

    /**
     * Find the prefix at a specific location in the prefixes array.
     *
     * @param $index
     *
     * @return string|null
     */
    protected function getPrefixAt($index)
    {
        $prefixes = $this->getPrefixes();
        return isset($prefixes[$index]) ? $prefixes[$index] : null;
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns a human readable format of the amount of bits or bytes.
     *
     * If the value provided is not numeric, the value will remain unfiltered
     *
     * @param  string $value
     * @return string|mixed
     */
    public function filter($value)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        // Parse to float and check if value is not zero
        $amount = (float) $value;
        if ($amount == 0) {
            return $this->formatAmount($amount);
        }

        // Calculate the correct size and prefix:
        $base = $this->isBinaryMode() ? self::BASE_BINARY : self::BASE_DECIMAL;
        $power = floor(log($amount, $base));
        $prefix = $this->getPrefixAt((int)$power);

        // When the amount is too big, no prefix can be found:
        if (is_null($prefix)) {
            return $this->formatAmount($amount);
        }

        // return formatted value:
        $result = ($amount / pow($base, $power));
        $formatted = number_format($result, $this->getPrecision());
        return $this->formatAmount($formatted, $prefix);
    }

    /**
     * @param      $amount
     * @param null $prefix
     *
     * @return string
     */
    protected function formatAmount($amount, $prefix = null)
    {
        return sprintf('%s %s%s', $amount, $prefix, $this->getUnit());
    }
}
