<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

use Magento\Store\Model\ScopeInterface;

class ResourceProvider
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Array of resource factory names
     *
     * @var array
     */
    protected $resourceFactoryNames;

    /**
     * Engine used for search
     *
     * @var string
     */
    protected $engineKey;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $resourceFactoryNames
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $resourceFactoryNames
    ) {
        $this->objectManager = $objectManager;
        $this->resourceFactoryNames = $resourceFactoryNames;
        $this->engineKey = $scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Magento\Framework\Model\Resource\AbstractResource
     */
    public function getResource()
    {
        return $this->createResource('resource');
    }

    /**
     * @return \Magento\CatalogSearch\Model\Resource\Advanced\Collection
     */
    public function getResourceCollection()
    {
        return $this->createResource('resource_collection');
    }

    /**
     * @return \Magento\CatalogSearch\Model\Resource\Fulltext\Collection
     */
    public function getResultCollection()
    {
        return $this->createResource('result_collection');
    }

    /**
     * @return \Magento\CatalogSearch\Model\Resource\Advanced\Collection
     */
    public function getAdvancedResultCollection()
    {
        return $this->createResource('advanced_result_collection');
    }

    /**
     * Create resource
     *
     * @param string $resourceKey
     * @return mixed
     */
    protected function createResource($resourceKey)
    {
        if (!isset($this->resourceFactoryNames[$this->engineKey][$resourceKey])) {
            throw new \RuntimeException(__('Resource has not been set.'));
        }
        $factory = $this->objectManager->create($this->resourceFactoryNames[$this->engineKey][$resourceKey]);
        return $factory->create();
    }
}
