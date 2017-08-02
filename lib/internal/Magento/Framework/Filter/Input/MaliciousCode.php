<?php
/**
 * Filter for removing malicious code from HTML
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filter\Input;

/**
 * Class \Magento\Framework\Filter\Input\MaliciousCode
 *
 * @since 2.0.0
 */
class MaliciousCode implements \Zend_Filter_Interface
{
    /**
     * Regular expressions for cutting malicious code
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_expressions = [
        //comments, must be first
        '/(\/\*.*\*\/)/Us',
        //tabs
        '/(\t)/',
        //javasript prefix
        '/(javascript\s*:)/Usi',
        //import styles
        '/(@import)/Usi',
        //js in the style attribute
        '/style=[^<]*((expression\s*?\([^<]*?\))|(behavior\s*:))[^<]*(?=\/*\>)/Uis',
        //js attributes
        '/(ondblclick|onclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|'.
        'onload|onunload|onerror)=[^<]*(?=\/*\>)/Uis',
        //tags
        '/<\/?(script|meta|link|frame|iframe|object).*>/Uis',
        //base64 usage
        '/src=[^<]*base64[^<]*(?=\/*\>)/Uis',
    ];

    /**
     * Filter value
     *
     * @param string|array $value
     * @return string|array Filtered value
     * @since 2.0.0
     */
    public function filter($value)
    {
        $replaced = 0;
        do {
            $value = preg_replace($this->_expressions, '', $value, -1, $replaced);
        } while ($replaced !== 0);
        return  $value;
    }

    /**
     * Add expression
     *
     * @param string $expression
     * @return $this
     * @since 2.0.0
     */
    public function addExpression($expression)
    {
        if (!in_array($expression, $this->_expressions)) {
            $this->_expressions[] = $expression;
        }
        return $this;
    }

    /**
     * Set expressions
     *
     * @param array $expressions
     * @return $this
     * @since 2.0.0
     */
    public function setExpressions(array $expressions)
    {
        $this->_expressions = $expressions;
        return $this;
    }
}
