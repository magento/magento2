<?php
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\App\Config\MutableScopeConfigInterface;

class MutableScopeConfig extends Config implements MutableScopeConfigInterface
{
    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param null|string $scopeCode
     * @return void
     */
    public function setValue(
        $path,
        $value,
        $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
        $scopeCode = null
    ) {
        if (empty($scopeCode)) {
            $scopeCode = null;
        }
        $this->_scopePool->getScope($scope, $scopeCode)->setValue($path, $value);
    }
}
