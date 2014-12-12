<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Config;

interface ScopeListInterface
{
    /**
     * Retrieve list of all scopes
     *
     * @return string[]
     */
    public function getAllScopes();
}
