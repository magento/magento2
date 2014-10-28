<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Filter.php 22662 2010-07-24 17:37:36Z mabe $
 */

/**
 * @see Zend_Ldap_Filter_String
 */
#require_once 'Zend/Ldap/Filter/String.php';

/**
 * Zend_Ldap_Filter.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Filter extends Zend_Ldap_Filter_String
{
    const TYPE_EQUALS         = '=';
    const TYPE_GREATER        = '>';
    const TYPE_GREATEROREQUAL = '>=';
    const TYPE_LESS           = '<';
    const TYPE_LESSOREQUAL    = '<=';
    const TYPE_APPROX         = '~=';

    /**
     * Creates an 'equals' filter.
     * (attr=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function equals($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, null, null);
    }

    /**
     * Creates a 'begins with' filter.
     * (attr=value*)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function begins($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, null, '*');
    }

    /**
     * Creates an 'ends with' filter.
     * (attr=*value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function ends($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, '*', null);
    }

    /**
     * Creates a 'contains' filter.
     * (attr=*value*)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function contains($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, '*', '*');
    }

    /**
     * Creates a 'greater' filter.
     * (attr>value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function greater($attr, $value)
    {
        return new self($attr, $value, self::TYPE_GREATER, null, null);
    }

    /**
     * Creates a 'greater or equal' filter.
     * (attr>=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function greaterOrEqual($attr, $value)
    {
        return new self($attr, $value, self::TYPE_GREATEROREQUAL, null, null);
    }

    /**
     * Creates a 'less' filter.
     * (attr<value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function less($attr, $value)
    {
        return new self($attr, $value, self::TYPE_LESS, null, null);
    }

    /**
     * Creates an 'less or equal' filter.
     * (attr<=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function lessOrEqual($attr, $value)
    {
        return new self($attr, $value, self::TYPE_LESSOREQUAL, null, null);
    }

    /**
     * Creates an 'approx' filter.
     * (attr~=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Zend_Ldap_Filter
     */
    public static function approx($attr, $value)
    {
        return new self($attr, $value, self::TYPE_APPROX, null, null);
    }

    /**
     * Creates an 'any' filter.
     * (attr=*)
     *
     * @param  string $attr
     * @return Zend_Ldap_Filter
     */
    public static function any($attr)
    {
        return new self($attr, '', self::TYPE_EQUALS, '*', null);
    }

    /**
     * Creates a simple custom string filter.
     *
     * @param  string $filter
     * @return Zend_Ldap_Filter_String
     */
    public static function string($filter)
    {
        return new Zend_Ldap_Filter_String($filter);
    }

    /**
     * Creates a simple string filter to be used with a mask.
     *
     * @param string $mask
     * @param string $value
     * @return Zend_Ldap_Filter_Mask
     */
    public static function mask($mask, $value)
    {
        /**
         * Zend_Ldap_Filter_Mask
         */
        #require_once 'Zend/Ldap/Filter/Mask.php';
        return new Zend_Ldap_Filter_Mask($mask, $value);
    }

    /**
     * Creates an 'and' filter.
     *
     * @param  Zend_Ldap_Filter_Abstract $filter,...
     * @return Zend_Ldap_Filter_And
     */
    public static function andFilter($filter)
    {
        /**
         * Zend_Ldap_Filter_And
         */
        #require_once 'Zend/Ldap/Filter/And.php';
        return new Zend_Ldap_Filter_And(func_get_args());
    }

    /**
     * Creates an 'or' filter.
     *
     * @param  Zend_Ldap_Filter_Abstract $filter,...
     * @return Zend_Ldap_Filter_Or
     */
    public static function orFilter($filter)
    {
        /**
         * Zend_Ldap_Filter_Or
         */
        #require_once 'Zend/Ldap/Filter/Or.php';
        return new Zend_Ldap_Filter_Or(func_get_args());
    }

    /**
     * Create a filter string.
     *
     * @param  string $attr
     * @param  string $value
     * @param  string $filtertype
     * @param  string $prepend
     * @param  string $append
     * @return string
     */
    private static function _createFilterString($attr, $value, $filtertype, $prepend = null, $append = null)
    {
        $str = $attr . $filtertype;
        if ($prepend !== null) $str .= $prepend;
        $str .= self::escapeValue($value);
        if ($append !== null) $str .= $append;
        return $str;
    }

    /**
     * Creates a new Zend_Ldap_Filter.
     *
     * @param string $attr
     * @param string $value
     * @param string $filtertype
     * @param string $prepend
     * @param string $append
     */
    public function __construct($attr, $value, $filtertype, $prepend = null, $append = null)
    {
        $filter = self::_createFilterString($attr, $value, $filtertype, $prepend, $append);
        parent::__construct($filter);
    }
}