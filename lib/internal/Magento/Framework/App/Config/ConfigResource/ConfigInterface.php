<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\ConfigResource;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Resource for storing store configuration values
 */
interface ConfigInterface
{
    /**
     * Save config value to the storage resource
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function saveConfig($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

    /**
     * Delete config value from the storage resource
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function deleteConfig($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
}
