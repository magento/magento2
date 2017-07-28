<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Template\Tokenizer;

/**
 * Template constructions variables tokenizer
 * @since 2.0.0
 */
class Variable extends \Magento\Framework\Filter\Template\Tokenizer\AbstractTokenizer
{
    /**
     * Internal counter used to keep track of how deep in array parsing we are
     *
     * @var int
     * @since 2.0.0
     */
    protected $arrayDepth = 0;

    /**
     * Tokenize string and return getted variable stack path
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
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
     * @return string
     * @since 2.0.0
     */
    public function getString()
    {
        $value = '';
        if ($this->isWhiteSpace()) {
            return $value;
        }
        $quoteStart = $this->isQuote();

        if ($quoteStart) {
            $breakSymbol = $this->char();
        } else {
            $breakSymbol = false;
            $value .= $this->char();
        }

        while ($this->next()) {
            if (!$breakSymbol && $this->isStringBreak()) {
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
     * @since 2.0.0
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
            } elseif ($this->isStringBreak()) {
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
     * Parses arrays demarcated via open/closing brackets. Keys/value pairs are separated by a
     * single colon character. Multi-dimensional arrays are supported. Example input:
     *
     * [key:value, "key2":"value2", [
     *     [123, foo],
     * ]]
     *
     * @return array
     * @since 2.0.0
     */
    public function getArray()
    {
        $values = [];
        if (!$this->isArray()) {
            return $values;
        }

        $this->incArrayDepth();

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
                $val = $this->getString();
            }

            if ($key) {
                $values[$key] = $val;
            } else {
                $values[] = $val;
            }
        }

        $this->decArrayDepth();
        return $values;
    }

    /**
     * Return the internal array depth counter
     *
     * @return int
     * @since 2.0.0
     */
    protected function getArrayDepth()
    {
        return $this->arrayDepth;
    }

    /**
     * Increment the internal array depth counter
     *
     * @return void
     * @since 2.0.0
     */
    protected function incArrayDepth()
    {
        $this->arrayDepth++;
    }

    /**
     * Decrement the internal array depth counter
     *
     * If depth is already 0 do nothing
     *
     * @return void
     * @since 2.0.0
     */
    protected function decArrayDepth()
    {
        if ($this->arrayDepth == 0) {
            return;
        }
        $this->arrayDepth--;
    }

    /**
     * Return true if current char is a number
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isNumeric()
    {
        return $this->char() >= '0' && $this->char() <= '9';
    }

    /**
     * Return true if current char is quote or apostrophe
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isQuote()
    {
        return $this->char() == '"' || $this->char() == "'";
    }

    /**
     * Retrun true if current char is opening boundary for an array
     *
     * @return bool
     * @since 2.0.0
     */
    public function isArray()
    {
        return $this->char() == '[';
    }

    /**
     * Return true if current char is closing boundary for string
     *
     * @return bool
     * @since 2.0.0
     */
    public function isStringBreak()
    {
        if ($this->getArrayDepth() == 0 && ($this->isWhiteSpace() || $this->char() == ',' || $this->char() == ')')) {
            return true;
        } elseif ($this->getArrayDepth() > 0 && ($this->char() == ',' || $this->char() == ']')) {
            return true;
        }
        return false;
    }

    /**
     * Return array of arguments for method
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
