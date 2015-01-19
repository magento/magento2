<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Context;

use Magento\Framework\Data\CollectionDataSourceInterface;
use Magento\Framework\View\Element\UiComponent\ConfigInterface;
use Magento\Framework\View\Element\UiComponent\ConfigStorageInterface;
use Magento\Framework\View\Element\UiComponent\DataProviderInterface;

/**
 * Class ConfigurationStorage
 */
class ConfigurationStorage implements ConfigStorageInterface
{
    /**
     * Components configuration storage
     *
     * @var array
     */
    protected $componentStorage = [];

    /**
     * Data storage
     *
     * @var array
     */
    protected $dataStorage = [];

    /**
     * Meta storage
     *
     * @var array
     */
    protected $metaStorage = [];

    /**
     * Data collection storage
     *
     * @var CollectionDataSourceInterface[]
     */
    protected $collectionStorage = [];

    /**
     * Global data storage
     *
     * @var array
     */
    protected $globalDataStorage = [];

    /**
     * Data provider storage
     *
     * @var array
     */
    protected $dataProviderStorage = [];

    /**
     * @var array
     */
    protected $components = [];

    /**
     * @var array
     */
    protected $layoutStructure = [];

    /**
     * @inheritdoc
     */
    public function addComponent($name, $data)
    {
        $this->components[$name] = $data;
    }

    /**
     * @inheritdoc
     */
    public function addComponentsData(ConfigInterface $config)
    {
        if (!isset($this->componentStorage[$config->getName()])) {
            $this->componentStorage[$config->getName()] = $config;
        }
    }

    /**
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @return array
     */
    public function getMetaKeys()
    {
        return array_keys($this->metaStorage);
    }

    /**
     * @inheritdoc
     */
    public function removeComponentsData(ConfigInterface $configuration)
    {
        unset($this->componentStorage[$configuration->getName()]);
    }

    /**
     * @inheritdoc
     */
    public function getComponentsData($name = null)
    {
        if ($name === null) {
            return $this->componentStorage;
        }
        return isset($this->componentStorage[$name]) ? $this->componentStorage[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function addDataSource($name, array $dataSource)
    {
        if (!isset($this->dataStorage[$name])) {
            $this->dataStorage[$name] = $dataSource;
        }
    }

    /**
     * @inheritdoc
     */
    public function removeDataSource($name)
    {
        unset($this->dataStorage[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getDataSource($name = null)
    {
        if ($name === null) {
            return $this->dataStorage;
        }
        return isset($this->dataStorage[$name]) ? $this->dataStorage[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function updateDataSource($name, array $dataSource)
    {
        if (isset($this->dataStorage[$name])) {
            $this->dataStorage[$name] = $dataSource;
        }
    }

    /**
     * @inheritdoc
     */
    public function addMeta($key, array $data)
    {
        if (!isset($this->metaStorage[$key])) {
            $this->metaStorage[$key] = $data;
        }
    }

    /**
     * @inheritdoc
     */
    public function removeMeta($key)
    {
        unset($this->metaStorage[$key]);
    }

    /**
     * @inheritdoc
     */
    public function getMeta($key = null)
    {
        if ($key === null) {
            return $this->metaStorage;
        }
        return isset($this->metaStorage[$key]) ? $this->metaStorage[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function updateMeta($key, array $data)
    {
        if (isset($this->metaStorage[$key])) {
            $this->metaStorage[$key] = $data;
        }
    }

    /**
     * @inheritdoc
     */
    public function addDataCollection($key, CollectionDataSourceInterface $dataCollection)
    {
        if (!isset($this->collectionStorage[$key])) {
            $this->collectionStorage[$key] = $dataCollection;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDataCollection($key = null)
    {
        if ($key === null) {
            return $this->collectionStorage;
        }
        return isset($this->collectionStorage[$key]) ? $this->collectionStorage[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function updateDataCollection($key, CollectionDataSourceInterface $dataCollection)
    {
        if (isset($this->collectionStorage[$key])) {
            $this->collectionStorage[$key] = $dataCollection;
        }
    }

    /**
     * @inheritdoc
     */
    public function addGlobalData($key, array $data)
    {
        if (!isset($this->globalDataStorage[$key])) {
            $this->globalDataStorage[$key] = $data;
        }
    }

    /**
     * @inheritdoc
     */
    public function removeGlobalData($key)
    {
        unset($this->globalDataStorage[$key]);
    }

    /**
     * @inheritdoc
     */
    public function getGlobalData($key = null)
    {
        if ($key === null) {
            return $this->globalDataStorage;
        }
        return isset($this->globalDataStorage[$key]) ? $this->globalDataStorage[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function addDataProvider($key, DataProviderInterface $dataProvider)
    {
        if (!isset($this->dataProviderStorage[$key])) {
            $this->dataProviderStorage[$key] = $dataProvider;
        }
    }

    /**
     * @inheritdoc
     */
    public function removeDataProvider($key)
    {
        if (isset($this->dataProviderStorage[$key])) {
            unset($this->dataProviderStorage[$key]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDataProvider($key = null)
    {
        if ($key === null) {
            return $this->dataProviderStorage;
        }
        return isset($this->dataProviderStorage[$key]) ? $this->dataProviderStorage[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function updateDataProvider($key, DataProviderInterface $dataProvider)
    {
        if (isset($this->dataProviderStorage[$key])) {
            $this->dataProviderStorage[$key] = $dataProvider;
        }
    }

    /**
     * @inheritdoc
     */
    public function addLayoutStructure($dataScope, array $structure)
    {
        $this->layoutStructure[$dataScope] = $structure;
    }

    /**
     * @inheritdoc
     */
    public function getLayoutStructure()
    {
        return $this->layoutStructure;
    }

    /**
     * @inheritdoc
     */
    public function getLayoutNode($name, $default = null)
    {
        if (strpos($name, '.') !== false) {
            $nameParts = explode('.', $name);
            $firstChunk = array_shift($nameParts);
            $node = isset($this->layoutStructure[$firstChunk]) ? $this->layoutStructure[$firstChunk] : [];
            foreach ($nameParts as $nodeName) {
                if (isset($node['children'][$nodeName])) {
                    $node = $node['children'][$nodeName];
                } else {
                    $node = $default;
                    break;
                }
            }
        } else {
            $node = isset($this->layoutStructure[$name]) ? $this->layoutStructure[$name] : [];
        }
        return $node;
    }
}
