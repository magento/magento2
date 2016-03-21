<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config scope list interface.
 *
 * @api
 */
interface ScopeListInterface
{
    /**
     * Retrieve list of all scopes
     *
     * @return string[]
     */
    public function getAllScopes();
}
