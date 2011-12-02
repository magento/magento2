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
 * @package    Zend_Acl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Acl
 */
#require_once 'Zend/Acl.php';


/**
 * @see Zend_Acl_Role_Interface
 */
#require_once 'Zend/Acl/Role/Interface.php';


/**
 * @see Zend_Acl_Resource_Interface
 */
#require_once 'Zend/Acl/Resource/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Acl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Acl_Assert_Interface
{
    /**
     * Returns true if and only if the assertion conditions are met
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $resource, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param  Zend_Acl                    $acl
     * @param  Zend_Acl_Role_Interface     $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  string                      $privilege
     * @return boolean
     */
    public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null);
}
