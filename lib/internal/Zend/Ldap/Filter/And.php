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
 * @version    $Id: And.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Ldap_Filter_Logical
 */
#require_once 'Zend/Ldap/Filter/Logical.php';

/**
 * Zend_Ldap_Filter_And provides an 'and' filter.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Filter_And extends Zend_Ldap_Filter_Logical
{
    /**
     * Creates an 'and' grouping filter.
     *
     * @param array $subfilters
     */
    public function __construct(array $subfilters)
    {
        parent::__construct($subfilters, self::TYPE_AND);
    }
}