<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
}
