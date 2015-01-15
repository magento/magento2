<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\Acl\Role;

/**
 * Acl Group model
 */
class Group extends \Magento\Authorization\Model\Acl\Role\Generic
{
    /**
     * All the group roles are prepended by G
     *
     */
    const ROLE_TYPE = 'G';
}
