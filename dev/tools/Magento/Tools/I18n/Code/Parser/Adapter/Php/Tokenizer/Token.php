<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer;

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
