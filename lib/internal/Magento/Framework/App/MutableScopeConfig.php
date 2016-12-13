<?php
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheritdoc
 */
class MutableScopeConfig extends Config implements MutableScopeConfigInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @inheritdoc
     */
    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if (isset($this->data[$scope][$scopeCode][$path])) {
            return $this->data[$scope][$scopeCode][$path];
        }

        return parent::getValue($path, $scope, $scopeCode);
    }

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
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->data[$scope][$scopeCode][$path] = $value;
    }

    /**
     * @inheritdoc
     */
    public function clean()
    {
        $this->data = null;
        parent::clean();
    }
}
