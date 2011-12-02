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
 * @see Zend_Ldap_Node_Schema_AttributeType_Interface
 */
#require_once 'Zend/Ldap/Node/Schema/AttributeType/Interface.php';

/**
 * Zend_Ldap_Node_Schema_AttributeType_ActiveDirectory provides access to the attribute type
 * schema information on an Active Directory server.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_Schema_AttributeType_ActiveDirectory extends Zend_Ldap_Node_Schema_Item
    implements Zend_Ldap_Node_Schema_AttributeType_Interface
{
    /**
     * Gets the attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->ldapdisplayname[0];
    }

    /**
     * Gets the attribute OID
     *
     * @return string
     */
    public function getOid()
    {

    }

    /**
     * Gets the attribute syntax
     *
     * @return string
     */
    public function getSyntax()
    {

    }

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength()
    {

    }

    /**
     * Returns if the attribute is single-valued.
     *
     * @return boolean
     */
    public function isSingleValued()
    {

    }

    /**
     * Gets the attribute description
     *
     * @return string
     */
    public function getDescription()
    {

    }
}