<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\App;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\ObjectManager;

/**
 * @inheritdoc
 */
class Config extends \Magento\Framework\App\Config
{
    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * Initialize data object with all settings data
     *
     * @param array $data
     * @param string $configType
     * @return void
     */
    private function setData(array $data, $configType)
    {
        $this->data[$configType] = new DataObject($data);
    }

    /**
     * Retrieve Scope Code Resolver
     *
     * @return ScopeCodeResolver
     */
    private function getScopeCodeResolver()
    {
        if (!$this->scopeCodeResolver) {
            $this->scopeCodeResolver = ObjectManager::getInstance()->get(ScopeCodeResolver::class);
        }

        return $this->scopeCodeResolver;
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
        $result = $this->get('system');

        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }

        if (empty($scopeCode)) {
            $scopeCode = $this->getScopeCodeResolver()->resolve($scope, $scopeCode);
        }

        $keys = explode('/', $path);
        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $searchKeys = array_merge([$scope, $scopeCode], $keys);
        } else {
            $searchKeys = array_merge([$scope], $keys);
        }

        $this->updateResult($searchKeys, $result, $value);
        $this->setData($result, 'system');
    }

    /**
     * Recursively update results in global variable, which hold configs
     *
     * @param array $keys
     * @param array $result
     * @param mixed $value
     * @return void
     */
    private function updateResult(array $keys, & $result, $value)
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            $result[$key] = $value;
        } else {
            $this->updateResult($keys, $result[$key], $value);
        }
    }

    /**
     * Flush all muted settings
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
        $this->scopeCodeResolver = null;
        parent::clean();
    }

    /**
     * @inheritdoc
     */
    public function get($configType, $path = null, $default = null)
    {
        $path = $path === null ? '' : $path;
        if (!isset($this->data[$configType]) || $this->data[$configType]->getData($path) === null) {
            return parent::get($configType, $path, $default);
        }

        return $this->data[$configType]->getData($path);
    }
}
