<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage;

use League\Flysystem\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Storage\AdapterFactory\AdapterFactoryInterface;

/**
 * Provider of storage adapters based on storage name
 */
class StorageAdapterProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $config
     */
    public function __construct(ObjectManagerInterface $objectManager, array $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Create storage adapter based on its name with provided options
     *
     * @param string $adapterName
     * @param array $options
     * @return AdapterInterface|null
     */
    public function create(string $adapterName, array $options) :? AdapterInterface
    {
        if (!isset($this->config[$adapterName])) {
            throw new InvalidStorageConfigurationException(
                "Configured adapter '$adapterName' is not supported"
            );
        }
        $adapterFactoryClass = $this->config[$adapterName];
        $adapterFactory = $this->objectManager->get($adapterFactoryClass);
        if (!$adapterFactory instanceof AdapterFactoryInterface) {
            throw new InvalidStorageConfigurationException(
                "Configured storage adapter factory '$adapterFactory' must implement " .
                "'\Magento\Framework\Storage\AdapterFactory\AdapterFactoryInterface'"
            );
        }
        return $adapterFactory->create($options);
    }
}
