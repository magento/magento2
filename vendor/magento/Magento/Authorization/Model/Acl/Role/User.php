<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorization\Model\Acl\Role;

/**
 * User acl role
 */
class User extends \Magento\Authorization\Model\Acl\Role\Generic
{
    /**
     * All the user roles are prepended by U
     *
     */
    const ROLE_TYPE = 'U';
}
