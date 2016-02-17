<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json;

/**
 * Class for Zend\Json\Json encode method.
 *
 * This class simply holds a string with a native Javascript Expression,
 * so objects | arrays to be encoded with Zend\Json\Json can contain native
 * Javascript Expressions.
 *
 * Example:
 * <code>
 * $foo = array(
 *     'integer'  => 9,
 *     'string'   => 'test string',
 *     'function' => Zend\Json\Expr(
 *         'function () { window.alert("javascript function encoded by Zend\Json\Json") }'
 *     ),
 * );
 *
 * Zend\Json\Json::encode($foo, false, array('enableJsonExprFinder' => true));
 * // it will returns json encoded string:
 * // {"integer":9,"string":"test string","function":function () {window.alert("javascript function encoded by Zend\Json\Json")}}
 * </code>
 */
class Expr
{
    /**
     * Storage for javascript expression.
     *
     * @var string
     */
    protected $expression;

    /**
     * Constructor
     *
     * @param  string $expression the expression to hold.
     */
    public function __construct($expression)
    {
        $this->expression = (string) $expression;
    }

    /**
     * Cast to string
     *
     * @return string holded javascript expression.
     */
    public function __toString()
    {
        return $this->expression;
    }
}
