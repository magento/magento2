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
 * @since 2.0.0
 */
interface ScopeInterface
{
    /**
     * Get current configuration scope identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrentScope();

    /**
     * Set current configuration scope
     *
     * @param string $scope
     * @return void
     * @since 2.0.0
     */
    public function setCurrentScope($scope);
}
