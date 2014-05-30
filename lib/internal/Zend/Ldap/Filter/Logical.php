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
 * @version    $Id: Logical.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Ldap_Filter_Abstract
 */
#require_once 'Zend/Ldap/Filter/Abstract.php';
/**
 * @see Zend_Ldap_Filter_String
 */
#require_once 'Zend/Ldap/Filter/String.php';

/**
 * Zend_Ldap_Filter_Logical provides a base implementation for a grouping filter.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Ldap_Filter_Logical extends Zend_Ldap_Filter_Abstract
{
    const TYPE_AND = '&';
    const TYPE_OR  = '|';

    /**
     * All the sub-filters for this grouping filter.
     *
     * @var array
     */
    private $_subfilters;

    /**
     * The grouping symbol.
     *
     * @var string
     */
    private $_symbol;

    /**
     * Creates a new grouping filter.
     *
     * @param array  $subfilters
     * @param string $symbol
     */
    protected function __construct(array $subfilters, $symbol)
    {
        foreach ($subfilters as $key => $s) {
            if (is_string($s)) $subfilters[$key] = new Zend_Ldap_Filter_String($s);
            else if (!($s instanceof Zend_Ldap_Filter_Abstract)) {
                /**
                 * @see Zend_Ldap_Filter_Exception
                 */
                #require_once 'Zend/Ldap/Filter/Exception.php';
                throw new Zend_Ldap_Filter_Exception('Only strings or Zend_Ldap_Filter_Abstract allowed.');
            }
        }
        $this->_subfilters = $subfilters;
        $this->_symbol = $symbol;
    }

    /**
     * Adds a filter to this grouping filter.
     *
     * @param  Zend_Ldap_Filter_Abstract $filter
     * @return Zend_Ldap_Filter_Logical
     */
    public function addFilter(Zend_Ldap_Filter_Abstract $filter)
    {
        $new = clone $this;
        $new->_subfilters[] = $filter;
        return $new;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        $return = '(' . $this->_symbol;
        foreach ($this->_subfilters as $sub) $return .= $sub->toString();
        $return .= ')';
        return $return;
    }
}