<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\ConfigResource;

/**
 * Resource for storing store configuration values
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function saveConfig($path, $value, $scope, $scopeId);

    /**
     * Delete config value from the storage resource
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return $this
     * @since 2.0.0
     */
    public function deleteConfig($path, $scope, $scopeId);
}
