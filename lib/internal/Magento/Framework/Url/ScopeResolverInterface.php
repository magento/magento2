<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Url;

/**
 * This ScopeResolverInterface adds the ability to get the Magento area the code is executing in.
 *
 * @api
 * @since 100.0.2
 */
interface ScopeResolverInterface extends \Magento\Framework\App\ScopeResolverInterface
{
    /**
     * Retrieve area code
     *
     * @return string|null
     */
    public function getAreaCode();
}
