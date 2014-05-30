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
 * @subpackage Schema
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_Ldap_Node_Schema_ObjectClass_Interface provides a contract for schema objectClasses.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Ldap_Node_Schema_ObjectClass_Interface
{
    /**
     * Gets the objectClass name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the objectClass OID
     *
     * @return string
     */
    public function getOid();

    /**
     * Gets the attributes that this objectClass must contain
     *
     * @return array
     */
    public function getMustContain();

    /**
     * Gets the attributes that this objectClass may contain
     *
     * @return array
     */
    public function getMayContain();

    /**
     * Gets the objectClass description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Gets the objectClass type
     *
     * @return integer
     */
    public function getType();

    /**
     * Returns the parent objectClasses of this class.
     * This includes structural, abstract and auxiliary objectClasses
     *
     * @return array
     */
    public function getParentClasses();
}