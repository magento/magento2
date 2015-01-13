<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\CollectionDataSourceInterface;

/**
 * Class ConfigurationStorageInterface
 */
interface ConfigStorageInterface
{
    /**
     * Register component
     *
     * @param string $name
     * @param array $data
     * @return mixed
     */
    public function addComponent($name, $data);

    /**
     * Add components configuration
     *
     * @param ConfigInterface $config
     * @return void
     */
    public function addComponentsData(ConfigInterface $config);

    /**
     * Remove components configuration
     *
     * @param ConfigInterface $configuration
     * @return void
     */
    public function removeComponentsData(ConfigInterface $configuration);

    /**
     * Get components configuration
     *
     * @param string|null $name
     * @return ConfigInterface|null|array
     */
    public function getComponentsData($name = null);

    /**
     * @return array
     */
    public function getComponents();

    /**
     * Add data in storage
     *
     * @param string $name
     * @param array $dataSource
     * @return void
     */
    public function addDataSource($name, array $dataSource);

    /**
     * Remove data in storage
     *
     * @param string $name
     * @return void
     */
    public function removeDataSource($name);

    /**
     * Get data from storage
     *
     * @param string|null $name
     * @return array|null
     */
    public function getDataSource($name = null);

    /**
     * Update data in storage
     *
     * @param string $name
     * @param array $dataSource
     * @return void
     */
    public function updateDataSource($name, array $dataSource);

    /**
     * Add meta data
     *
     * @param string $key
     * @param array $data
     * @return mixed
     */
    public function addMeta($key, array $data);

    /**
     * Remove meta data
     *
     * @param string $key
     * @return array
     */
    public function removeMeta($key);

    /**
     * Get meta data
     *
     * @param string|null $key
     * @return array|null
     */
    public function getMeta($key = null);

    /**
     * Update meta data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function updateMeta($key, array $data);

    /**
     * @return array
     */
    public function getMetaKeys();

    /**
     * Set data collection
     *
     * @param string $key
     * @param CollectionDataSourceInterface|CriteriaInterface $dataCollection
     * @return void
     */
    public function addDataCollection($key, CollectionDataSourceInterface $dataCollection);

    /**
     * Get data collection
     *
     * @param string|null $key
     * @return CollectionDataSourceInterface|CriteriaInterface
     */
    public function getDataCollection($key = null);

    /**
     * Update data collection in storage
     *
     * @param string $key
     * @param CollectionDataSourceInterface|CriteriaInterface $dataCollection
     * @return mixed
     */
    public function updateDataCollection($key, CollectionDataSourceInterface $dataCollection);

    /**
     * Add cloud data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function addGlobalData($key, array $data);

    /**
     * Remove cloud data in storage
     *
     * @param string $key
     * @return void
     */
    public function removeGlobalData($key);

    /**
     * Get cloud data from storage
     *
     * @param string|null $key
     * @return array|null
     */
    public function getGlobalData($key = null);

    /**
     * @param string $key
     * @param DataProviderInterface $dataProvider
     * @return void
     */
    public function addDataProvider($key, DataProviderInterface $dataProvider);

    /**
     * @param string $key
     * @return void
     */
    public function removeDataProvider($key);

    /**
     * @param null|string $key
     * @return DataProviderInterface[]|DataProviderInterface|null
     */
    public function getDataProvider($key = null);

    /**
     * @param string $key
     * @param DataProviderInterface $dataProvider
     * @return void
     */
    public function updateDataProvider($key, DataProviderInterface $dataProvider);

    /**
     * @param string $dataScope
     * @param array $structure
     * @return void
     */
    public function addLayoutStructure($dataScope, array $structure);

    /**
     * @return array
     */
    public function getLayoutStructure();

    /**
     * @param string $name
     * @param mixed $default
     * @return array
     */
    public function getLayoutNode($name, $default = null);
}
