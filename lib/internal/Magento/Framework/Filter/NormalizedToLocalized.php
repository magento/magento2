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

class NormalizedToLocalized implements FilterInterface
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
     * Class constructor
     *
     * @param array|null  $options
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
     * @param  array|null $options
     * @return self
     */
    public function setOptions(array $options = null)
    {
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }

    /**
     * Defined by FilterInterface
     *
     * Normalizes the given input
     *
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function filter($value)
    {
        if (is_array($value)) {
            $formatter = new IntlDateFormatter(
                $this->_options['locale'],
                IntlDateFormatter::SHORT,
                IntlDateFormatter::NONE
            );
            $formatter->setPattern($this->_options['date_format']);
            return $formatter->format(strtotime($value['month'] . '/' . $value['day'] . '/' . $value['year']));
        } elseif ($this->_options['precision'] === 0) {
            $numberParse = new NumberParse($this->_options['locale'], NumberFormatter::PATTERN_DECIMAL);
            return (string) $numberParse->filter($value);
        } elseif ($this->_options['precision'] === null) {
            $numberParse = new NumberParse($this->_options['locale'], NumberFormatter::DECIMAL);
            return (string) $numberParse->filter($value);
        }

        return $value;
    }
}
