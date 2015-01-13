<?php
/**
 * Application config storage writer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Storage;

class Writer implements \Magento\Framework\App\Config\Storage\WriterInterface
{
    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\Resource\ConfigInterface
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\Config\Resource\ConfigInterface $resource
     */
    public function __construct(\Magento\Framework\App\Config\Resource\ConfigInterface $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * Delete config value from storage
     *
     * @param   string $path
     * @param   string $scope
     * @param   int $scopeId
     * @return  void
     */
    public function delete($path, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeId = 0)
    {
        $this->_resource->deleteConfig(rtrim($path, '/'), $scope, $scopeId);
    }

    /**
     * Save config value to storage
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function save($path, $value, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeId = 0)
    {
        $this->_resource->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);
    }
}
