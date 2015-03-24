<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

interface ScopeInterface
{
    /**#@+
     * Scope types
     */
    const SCOPE_STORES = 'stores';

    const SCOPE_WEBSITES = 'websites';

    const SCOPE_STORE   = 'store';
    const SCOPE_GROUP   = 'group';
    const SCOPE_WEBSITE = 'website';
    /**#@-*/
}
