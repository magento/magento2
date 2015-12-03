<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design;

use Magento\Framework\App\Config\ValueFactory;
use Magento\Config\Model\Config\Loader as ConfigLoader;
use Magento\Framework\ObjectManagerInterface;

class BackendModelFactory extends ValueFactory
{
    /**
     * @var ConfigLoader
     */
    protected $configLoader;

    /**
     * @var array
     */
    protected $extendedConfig = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigLoader $configLoader
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigLoader $configLoader
    ) {
        parent::__construct($objectManager);
        $this->configLoader = $configLoader;
    }

    /**
     * @inheritDoc
     */
    public function create(array $data = [])
    {
        $backendModelData = [
            'path' => $data['config']['path'],
            'scope' => $data['scope'],
            'scope_id' => $data['scopeId'],
            'field_config' => $data['config'],
        ];
        $configId = $this->getConfigId($data['scope'], $data['scopeId'], $data['config']['path']);
        if ($configId) {
            $backendModelData['config_id'] = $configId;
        }

        $backendModel = isset($data['config']['backend_model'])
            ? $this->_objectManager->create($data['config']['backend_model'], ['data' => $backendModelData])
            : parent::create(['data' => $backendModelData]);
        $backendModel->setData('value', $data['value']);

        return $backendModel;
    }

    /**
     * @param string $scope
     * @param string $scopeId
     * @param string $path
     * @return null
     */
    protected function getConfigId($scope, $scopeId, $path)
    {
        $extendedConfig = $this->getExtendedConfig($scope, $scopeId);
        return isset($extendedConfig[$path]['config_id']) ? $extendedConfig[$path]['config_id'] : null;
    }

    /**
     * @param string $scope
     * @param string $scopeId
     * @return array
     */
    protected function getExtendedConfig($scope, $scopeId)
    {
        if (!$this->extendedConfig) {
            $this->extendedConfig = $this->configLoader->getConfigByPath(
                'design',
                $scope,
                $scopeId,
                true
            );
        }
        return $this->extendedConfig;
    }
}
