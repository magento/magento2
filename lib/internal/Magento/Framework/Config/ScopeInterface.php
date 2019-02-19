<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config scope interface.
 *
 * @api
 */
interface ScopeInterface
{
    /**
     * Get current configuration scope identifier
     *
     * @return string
     */
    public function getCurrentScope();

    /**
     * Set current configuration scope
     *
     * @param string $scope
     * @return void
     */
    public function setCurrentScope($scope);
}
