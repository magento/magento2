<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Theme\Model\Design\Config\MetadataProvider;
use Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory;

class BackendModelFactory extends ValueFactory
{
    /**
     * @var array
     */
    protected $storedData = [];

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $backendTypes = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param MetadataProvider $metadataProvider
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MetadataProvider $metadataProvider,
        CollectionFactory $collectionFactory
    ) {
        $this->metadataProvider = $metadataProvider;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($objectManager);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data = [])
    {
        $backendModelData = array_replace_recursive(
            $this->getStoredData($data['scope'], $data['scopeId'], $data['config']['path']),
            [
                'path' => $data['config']['path'],
                'scope' => $data['scope'],
                'scope_id' => $data['scopeId'],
                'field_config' => $data['config'],
            ]
        );

        $backendType = isset($data['config']['backend_model'])
            ? $data['config']['backend_model']
            : $this->_instanceName;

        /** @var Value $backendModel */
        $backendModel = $this->getNewBackendModel($backendType, $backendModelData);
        $backendModel->setValue($data['value']);

        return $backendModel;
    }

    /**
     * Retrieve new empty backend model
     *
     * @param string $backendType
     * @param array $data
     * @return Value
     */
    protected function getNewBackendModel($backendType, array $data = [])
    {
        return $this->_objectManager->create($backendType, ['data' => $data]);
    }

    /**
     * Create backend model by config path
     *
     * @param string $path
     * @param array $data
     * @return Value
     */
    public function createByPath($path, array $data = [])
    {
        return $this->getNewBackendModel($this->getBackendTypeByPath($path), $data);
    }

    /**
     * Retrieve backend type by config path
     *
     * @param string $path
     * @return string
     */
    protected function getBackendTypeByPath($path)
    {
        if (!isset($this->backendTypes[$path])) {
            $metadata = $this->metadataProvider->get();
            $index = array_search($path, array_column($metadata, 'path'));
            $backendType = $this->_instanceName;
            if ($index !== false && isset(array_values($metadata)[$index]['backend_model'])) {
                $backendType = array_values($metadata)[$index]['backend_model'];
            }
            $this->backendTypes[$path] = $backendType;
        }
        return $this->backendTypes[$path];
    }

    /**
     * Get config data for path
     *
     * @param string $scope
     * @param string $scopeId
     * @param string $path
     * @return array
     */
    protected function getStoredData($scope, $scopeId, $path)
    {
        $storedData = $this->getScopeData($scope, $scopeId);
        $dataKey = array_search($path, array_column($storedData, 'path'));
        return $dataKey !== false ? $storedData[$dataKey] : [];
    }

    /**
     * Get stored data for scope and scope id
     *
     * @param string $scope
     * @param string $scopeId
     * @return array
     */
    protected function getScopeData($scope, $scopeId)
    {
        if (!isset($this->storedData[$scope][$scopeId])) {
            $collection = $this->collectionFactory->create();
            $collection->addPathsFilter($this->getMetadata());
            $collection->addFieldToFilter('scope', $scope);
            $collection->addScopeIdFilter($scopeId);
            $this->storedData[$scope][$scopeId] = $collection->getData();
        }
        return $this->storedData[$scope][$scopeId];
    }

    /**
     * Retrieve metadata
     *
     * @return array
     */
    protected function getMetadata()
    {
        if (!$this->metadata) {
            $this->metadata = $this->metadataProvider->get();
            array_walk($this->metadata, function (&$value) {
                $value = $value['path'];
            });
        }
        return $this->metadata;
    }
}
