<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

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
