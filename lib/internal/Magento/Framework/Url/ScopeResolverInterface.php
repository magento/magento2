<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
