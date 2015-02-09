<?php
/**
 * Configuration interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config;

interface ScopeConfigInterface
{
    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getValue($path, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeCode = null);

    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeCode = null);
}
