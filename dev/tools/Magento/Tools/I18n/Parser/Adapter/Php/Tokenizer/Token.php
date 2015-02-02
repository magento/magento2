<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer;

/**
 * Token
 */
class Token
{
    /**
     * Value
     *
     * @var int|string
     */
    private $_value;

    /**
     * Name
     *
     * @var int|string
     */
    private $_name;

    /**
     * Line
     *
     * @var int
     */
    private $_line;

    /**
     * Token construct
     *
     * @param int|string $name
     * @param int|string $value
     * @param int $line
     */
    public function __construct($name, $value, $line = 0)
    {
        $this->_name = $name;
        $this->_value = $value;
        $this->_line = $line;
    }

    /**
     * Get token name
     *
     * @return int|string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get token value
     *
     * @return int|string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Get line of token beginning
     *
     * @return int
     */
    public function getLine()
    {
        return $this->_line;
    }

    /**
     * Whenever token is equal function
     *
     * @param string $functionName
     * @return bool
     */
    public function isEqualFunction($functionName)
    {
        return $this->getName() == T_STRING && $this->getValue() == $functionName;
    }

    /**
     * Is object operator
     *
     * @return bool
     */
    public function isObjectOperator()
    {
        return $this->getName() == T_OBJECT_OPERATOR;
    }

    /**
     * Is whitespace
     *
     * @return bool
     */
    public function isWhitespace()
    {
        return $this->getName() == T_WHITESPACE;
    }

    /**
     * Is constant encapsed string
     *
     * @return bool
     */
    public function isConstantEncapsedString()
    {
        return $this->getName() == T_CONSTANT_ENCAPSED_STRING;
    }

    /**
     * Is open brace
     *
     * @return bool
     */
    public function isOpenBrace()
    {
        return $this->getValue() == '(';
    }

    /**
     * Is close brace
     *
     * @return bool
     */
    public function isCloseBrace()
    {
        return $this->getValue() == ')';
    }

    /**
     * Is comma
     *
     * @return bool
     */
    public function isComma()
    {
        return $this->getValue() == ',';
    }

    /**
     * Is semicolon
     *
     * @return bool
     */
    public function isSemicolon()
    {
        return $this->getValue() == ';';
    }
}
