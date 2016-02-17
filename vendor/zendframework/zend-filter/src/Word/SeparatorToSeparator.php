<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\Word;

use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;

class SeparatorToSeparator extends AbstractFilter
{
    protected $searchSeparator = null;
    protected $replacementSeparator = null;

    /**
     * Constructor
     *
     * @param  string $searchSeparator      Separator to search for
     * @param  string $replacementSeparator Separator to replace with
     */
    public function __construct($searchSeparator = ' ', $replacementSeparator = '-')
    {
        $this->setSearchSeparator($searchSeparator);
        $this->setReplacementSeparator($replacementSeparator);
    }

    /**
     * Sets a new seperator to search for
     *
     * @param  string $separator Seperator to search for
     * @return self
     */
    public function setSearchSeparator($separator)
    {
        $this->searchSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set separator to search for
     *
     * @return string
     */
    public function getSearchSeparator()
    {
        return $this->searchSeparator;
    }

    /**
     * Sets a new separator which replaces the searched one
     *
     * @param  string $separator Separator which replaces the searched one
     * @return self
     */
    public function setReplacementSeparator($separator)
    {
        $this->replacementSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set separator which replaces the searched one
     *
     * @return string
     */
    public function getReplacementSeparator()
    {
        return $this->replacementSeparator;
    }

    /**
     * Defined by Zend\Filter\Filter
     *
     * Returns the string $value, replacing the searched separators with the defined ones
     *
     * @param  string|array $value
     * @return string|array
     */
    public function filter($value)
    {
        if (!is_scalar($value) && !is_array($value)) {
            return $value;
        }

        if ($this->searchSeparator === null) {
            throw new Exception\RuntimeException('You must provide a search separator for this filter to work.');
        }

        $pattern = '#' . preg_quote($this->searchSeparator, '#') . '#';
        return preg_replace($pattern, $this->replacementSeparator, $value);
    }
}
