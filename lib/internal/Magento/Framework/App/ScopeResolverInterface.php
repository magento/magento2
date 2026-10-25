<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Scopes provider interface
 *
 * @api
 */
interface ScopeResolverInterface
{
    /**
     * Retrieve application scope object
     *
     * @param null|int $scopeId
     * @return \Magento\Framework\App\ScopeInterface
     */
    public function getScope($scopeId = null);

    /**
     * Retrieve scopes array
     *
     * @return \Magento\Framework\App\ScopeInterface[]
     */
    public function getScopes();
}
