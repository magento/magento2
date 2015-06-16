<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Template\Tokenizer;

/**
 * Template constructions variables tokenizer
 */
class Variable extends \Magento\Framework\Filter\Template\Tokenizer\AbstractTokenizer
{
    /**
     * Tokenize string and return getted variable stack path
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function tokenize()
    {
        $actions = [];
        $parameterName = '';
        $variableSet = false;
        do {
            if ($this->isWhiteSpace()) {
                // Ignore white spaces
                continue;
            } elseif ($this->char() != '.' && $this->char() != '(') {
                // Property or method name
                $parameterName .= $this->char();
            } elseif ($this->char() == '(') {
                // Method declaration
                $methodArgs = $this->getMethodArgs();
                $actions[] = ['type' => 'method', 'name' => $parameterName, 'args' => $methodArgs];
                $parameterName = '';
            } elseif ($parameterName != '') {
                // Property or variable declaration
                if ($variableSet) {
                    $actions[] = ['type' => 'property', 'name' => $parameterName];
                } else {
                    $variableSet = true;
                    $actions[] = ['type' => 'variable', 'name' => $parameterName];
                }
                $parameterName = '';
            }
        } while ($this->next());

        if ($parameterName != '') {
            if ($variableSet) {
                $actions[] = ['type' => 'property', 'name' => $parameterName];
            } else {
                $actions[] = ['type' => 'variable', 'name' => $parameterName];
            }
        }

        return $actions;
    }

    /**
     * Get string value for method args
     *
     * @param string|null $breaks characters to break on in abscense of quote
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getString($breaks = null)
    {
        $value = '';
        if ($this->isWhiteSpace()) {
            return $value;
        }
        $qouteStart = $this->isQuote();

        if ($qouteStart) {
            $breakSymbol = $this->char();
        } else {
            $breakSymbol = false;
            $value .= $this->char();
        }

        if ($breaks) {
            $breaks = str_split($breaks);
        }

        while ($this->next()) {
            if (!$breakSymbol && !$breaks && ($this->isWhiteSpace() || $this->char() == ',' || $this->char() == ')')) {
                $this->prev();
                break;
            } elseif (!$breakSymbol && $breaks && in_array($this->char(), $breaks)) {
                $this->prev();
                break;
            } elseif ($breakSymbol && $this->char() == $breakSymbol) {
                break;
            } elseif ($this->char() == '\\') {
                $this->next();
                $value .= $this->char();
            } else {
                $value .= $this->char();
            }
        }
        return $value;
    }

    /**
     * Get array member key or return false if none present
     *
     * @return bool|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getMemberKey()
    {
        $value = '';
        if ($this->isWhiteSpace()) {
            return $value;
        }

        $quoteStart = $this->isQuote();

        if ($quoteStart) {
            $closeQuote = $this->char();
        } else {
            $closeQuote = false;
            $value .= $this->char();
        }

        while ($this->next()) {
            if ($closeQuote) {
                if ($this->char() == $closeQuote) {
                    $closeQuote = false;
                    continue;
                }
                $value .= $this->char();
            } elseif ($this->char() == ':') {
                $this->next();
                return $value;
            } elseif ($this->char() == ',' || $this->char() == ']') {
                $this->prev();
                break;
            } else {
                $value .= $this->char();
            }
        }

        if ($quoteStart) {
            $this->back(strlen($value) + 1);
        } else {
            $this->back(strlen($value) - 1);
        }
        return false;
    }

    /**
     * Get array value for method args
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getArray()
    {
        $values = [];
        if (!$this->isArray()) {
            return $values;
        }

        while ($this->next()) {
            if ($this->char() == ']') {
                break;
            } elseif ($this->isWhiteSpace() || $this->char() == ',') {
                continue;
            }

            $key = $this->getMemberKey();

            if ($this->isNumeric()) {
                $val = $this->getNumber();
            } elseif ($this->isArray()) {
                $val = $this->getArray();
            } else {
                $val = $this->getString(',]');
            }

            if ($key) {
                $values[$key] = $val;
            } else {
                $values[] = $val;
            }
        }
        return $values;
    }

    /**
     * Return true if current char is a number
     *
     * @return boolean
     */
    public function isNumeric()
    {
        return $this->char() >= '0' && $this->char() <= '9';
    }

    /**
     * Return true if current char is quote or apostrophe
     *
     * @return boolean
     */
    public function isQuote()
    {
        return $this->char() == '"' || $this->char() == "'";
    }

    /**
     * Retrun true if current char is opening boundary for an array
     *
     * @return bool
     */
    public function isArray()
    {
        return $this->char() == '[';
    }

    /**
     * Return array of arguments for method
     *
     * @return array
     */
    public function getMethodArgs()
    {
        $value = [];

        while ($this->next() && $this->char() != ')') {
            if ($this->isWhiteSpace() || $this->char() == ',') {
                continue;
            } elseif ($this->isNumeric()) {
                $value[] = $this->getNumber();
            } elseif ($this->isArray()) {
                $value[] = $this->getArray();
            } else {
                $value[] = $this->getString();
            }
        }
        return $value;
    }

    /**
     * Return number value for method args
     *
     * @return float
     */
    public function getNumber()
    {
        $value = $this->char();
        while (($this->isNumeric() || $this->char() == '.') && $this->next()) {
            $value .= $this->char();
        }

        if (!$this->isNumeric()) {
            $this->prev();
        }
        return floatval($value);
    }
}
