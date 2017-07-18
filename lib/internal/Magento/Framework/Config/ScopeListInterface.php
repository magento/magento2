<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
