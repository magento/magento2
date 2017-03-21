<?php
/**
 * Application config storage writer interface
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Storage;

use Magento\Framework\App\Config\ScopeConfigInterface;

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
    public function delete($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

    /**
     * Save config value to storage
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
}
