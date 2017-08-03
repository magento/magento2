<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * This ScopeResolverInterface adds the ability to get the Magento area the code is executing in.
 *
 * @api
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
