<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Template\Tokenizer;

/**
 * Template constructions tokenizer
 * @since 2.0.0
 */
abstract class AbstractTokenizer
{
    /**
     * Current index in string
     *
     * @var int
     * @since 2.0.0
     */
    protected $_currentIndex;

    /**
     * String for tokenize
     *
     * @var string
     * @since 2.0.0
     */
    protected $_string;

    /**
     * Move current index to next char.
     *
     * If index out of bounds returns false
     *
     * @return boolean
     * @since 2.0.0
     */
    public function next()
    {
        if ($this->_currentIndex + 1 >= strlen($this->_string)) {
            return false;
        }

        $this->_currentIndex++;
        return true;
    }

    /**
     * Move current index to previous char.
     *
     * If index out of bounds returns false
     *
     * @return boolean
     * @since 2.0.0
     */
    public function prev()
    {
        if ($this->_currentIndex - 1 < 0) {
            return false;
        }

        $this->_currentIndex--;
        return true;
    }

    /**
     * Move current index backwards.
     *
     * If index out of bounds returns false
     *
     * @param int $distance number of characters to backtrack
     * @return bool
     * @since 2.0.0
     */
    public function back($distance)
    {
        if ($this->_currentIndex - $distance < 0) {
            return false;
        }

        $this->_currentIndex -= $distance;
        return true;
    }

    /**
     * Return current char
     *
     * @return string
     * @since 2.0.0
     */
    public function char()
    {
        return $this->_string[$this->_currentIndex];
    }

    /**
     * Set string for tokenize
     *
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    public function setString($value)
    {
        $this->_string = urldecode($value);
        $this->reset();
    }

    /**
     * Move char index to begin of string
     *
     * @return void
     * @since 2.0.0
     */
    public function reset()
    {
        $this->_currentIndex = 0;
    }

    /**
     * Return true if current char is white-space
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isWhiteSpace()
    {
        return trim($this->char()) != $this->char();
    }

    /**
     * Tokenize string
     *
     * @return array
     * @since 2.0.0
     */
    abstract public function tokenize();
}
