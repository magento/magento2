<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\Model;

/**
 * @api
 * @since 100.0.2
 */
interface ScopeInterface
{
    /**#@+
     * Scope types
     */
    const SCOPE_STORES = 'stores';
    const SCOPE_GROUPS   = 'groups';
    const SCOPE_WEBSITES = 'websites';

    const SCOPE_STORE   = 'store';
    const SCOPE_GROUP   = 'group';
    const SCOPE_WEBSITE = 'website';
    /**#@-*/
}
