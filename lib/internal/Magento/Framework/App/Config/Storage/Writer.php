<?php
/**
 * Application config storage writer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Storage;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class \Magento\Framework\App\Config\Storage\Writer
 *
 * @since 2.0.0
 */
class Writer implements \Magento\Framework\App\Config\Storage\WriterInterface
{
    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     * @since 2.0.0
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resource
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Config\ConfigResource\ConfigInterface $resource)
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
     * @since 2.0.0
     */
    public function delete($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
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
     * @since 2.0.0
     */
    public function save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $this->_resource->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);
    }
}
