<?php
/**
 * Application config storage writer interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Storage;

interface WriterInterface
{
    /**
     * Delete config value from storage
     *
     * @param   string $path
     * @param   string $scope
     * @param   int $scopeId
     * @return void
     */
    public function delete($path, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeId = 0);

    /**
     * Save config value to storage
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function save($path, $value, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeId = 0);
}
