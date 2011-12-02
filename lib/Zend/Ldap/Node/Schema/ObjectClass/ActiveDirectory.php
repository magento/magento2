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
 * @version    $Id: ActiveDirectory.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Ldap_Node_Schema_Item
 */
#require_once 'Zend/Ldap/Node/Schema/Item.php';
/**
 * @see Zend_Ldap_Node_Schema_ObjectClass_Interface
 */
#require_once 'Zend/Ldap/Node/Schema/ObjectClass/Interface.php';

/**
 * Zend_Ldap_Node_Schema_ObjectClass_ActiveDirectory provides access to the objectClass
 * schema information on an Active Directory server.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_Schema_ObjectClass_ActiveDirectory extends Zend_Ldap_Node_Schema_Item
    implements Zend_Ldap_Node_Schema_ObjectClass_Interface
{
    /**
     * Gets the objectClass name
     *
     * @return string
     */
    public function getName()
    {
        return $this->ldapdisplayname[0];
    }

    /**
     * Gets the objectClass OID
     *
     * @return string
     */
    public function getOid()
    {

    }

    /**
     * Gets the attributes that this objectClass must contain
     *
     * @return array
     */
    public function getMustContain()
    {

    }

    /**
     * Gets the attributes that this objectClass may contain
     *
     * @return array
     */
    public function getMayContain()
    {

    }

    /**
     * Gets the objectClass description
     *
     * @return string
     */
    public function getDescription()
    {

    }

    /**
     * Gets the objectClass type
     *
     * @return integer
     */
    public function getType()
    {

    }

    /**
     * Returns the parent objectClasses of this class.
     * This includes structural, abstract and auxiliary objectClasses
     *
     * @return array
     */
    public function getParentClasses()
    {

    }
}