<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\Interception\PluginListInterface;

/**
 * List provider for compiled pligins.
 */
class CompiledPluginList implements PluginListInterface
{
    /**
     * @var PluginList
     */
    private $pluginList;

    /**
     * @param PluginList $pluginList
     */
    public function __construct(
        PluginList $pluginList
    ) {
        $this->pluginList = $pluginList;
    }

    /**
     * Retrieve plugin Instance
     *
     * @param string $type
     * @param string $code
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPlugin($type, $code)
    {
        return null;
    }

    /**
     * Merge configuration
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        $this->pluginList->merge($config);
    }

    /**
     * Get class of a plugin
     *
     * @param string $type
     * @param string $code
     * @return mixed
     */
    public function getPluginType(string $type, string $code)
    {
        return $this->pluginList->getPluginType($type, $code);
    }

    /**
     * Set current scope
     *
     * @param ScopeInterface $scope
     */
    public function setScope(ScopeInterface $scope)
    {
        $this->pluginList->setScope($scope);
    }

    /**
     * Retrieve next plugins in chain
     *
     * @param string $type
     * @param string $method
     * @param string $code
     * @return array
     */
    public function getNext($type, $method, $code = null)
    {
        return $this->pluginList->getNext($type, $method, $code);
    }

    /**
     * PluginList instance should not be shared.
     */
    public function __clone()
    {
        $this->pluginList = clone $this->pluginList;
    }
}
