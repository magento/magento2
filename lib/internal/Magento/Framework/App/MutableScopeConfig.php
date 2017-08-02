<?php
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheritdoc
 * @since 2.0.0
 */
class MutableScopeConfig extends Config implements MutableScopeConfigInterface
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $data;

    /**
     * @inheritdoc
     * @since 2.2.0
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
     * @since 2.0.0
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
     * @since 2.2.0
     */
    public function clean()
    {
        $this->data = null;
        parent::clean();
    }
}
