<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

interface ScopeResolverInterface extends \Magento\Framework\App\ScopeResolverInterface
{
    /**
     * Retrieve scopes array
     *
     * @return \Magento\Framework\Url\ScopeInterface[]
     */
    public function getScopes();

    /**
     * Retrieve area code
     *
     * @return \Magento\Framework\Url\ScopeInterface[]
     */
    public function getAreaCode();
}
