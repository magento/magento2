<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
