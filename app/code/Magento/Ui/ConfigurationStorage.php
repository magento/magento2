<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui;

use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\View\Element\UiComponent\ConfigInterface;
use Magento\Framework\View\Element\UiComponent\ConfigStorageInterface;

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
     * @var DataCollection[]
     */
    protected $collectionStorage = [];

    /**
     * Global data storage
     *
     * @var array
     */
    protected $globalDataStorage = [];

    /**
     * Add components configuration
     *
     * @param ConfigInterface $configuration
     * @return void
     */
    public function addComponentsData(ConfigInterface $configuration)
    {
        if (!isset($this->componentStorage[$configuration->getName()])) {
            $this->componentStorage[$configuration->getName()] = $configuration;
        }
    }

    /**
     * Remove components configuration
     *
     * @param ConfigInterface $configuration
     * @return void
     */
    public function removeComponentsData(ConfigInterface $configuration)
    {
        unset($this->componentStorage[$configuration->getName()]);
    }

    /**
     * Get components configuration
     *
     * @param string|null $name
     * @return ConfigInterface|null|array
     */
    public function getComponentsData($name = null)
    {
        if ($name === null) {
            return $this->componentStorage;
        }
        return isset($this->componentStorage[$name]) ? $this->componentStorage[$name] : null;
    }

    /**
     * Add data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function addData($key, array $data)
    {
        if (!isset($this->dataStorage[$key])) {
            $this->dataStorage[$key] = $data;
        }
    }

    /**
     * Remove data in storage
     *
     * @param string $key
     * @return void
     */
    public function removeData($key)
    {
        unset($this->dataStorage[$key]);
    }

    /**
     * Get data from storage
     *
     * @param string|null $key
     * @return array|null
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return $this->dataStorage;
        }
        return isset($this->dataStorage[$key]) ? $this->dataStorage[$key] : null;
    }

    /**
     * Update data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function updateData($key, array $data)
    {
        if (isset($this->dataStorage[$key])) {
            $this->dataStorage[$key] = $data;
        }
    }

    /**
     * Add meta data
     *
     * @param string $key
     * @param array $data
     * @return mixed
     */
    public function addMeta($key, array $data)
    {
        if (!isset($this->metaStorage[$key])) {
            $this->metaStorage[$key] = $data;
        }
    }

    /**
     * Remove meta data
     *
     * @param string $key
     * @return array
     */
    public function removeMeta($key)
    {
        unset($this->metaStorage[$key]);
    }

    /**
     * Get meta data
     *
     * @param string|null $key
     * @return array
     */
    public function getMeta($key = null)
    {
        if ($key === null) {
            return $this->metaStorage;
        }
        return isset($this->metaStorage[$key]) ? $this->metaStorage[$key] : null;
    }

    /**
     * Update meta data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function updateMeta($key, array $data)
    {
        if (isset($this->metaStorage[$key])) {
            $this->metaStorage[$key] = $data;
        }
    }

    /**
     * Set data collection
     *
     * @param string $key
     * @param DataCollection $dataCollection
     * @return void
     */
    public function addDataCollection($key, DataCollection $dataCollection)
    {
        if (!isset($this->collectionStorage[$key])) {
            $this->collectionStorage[$key] = $dataCollection;
        }
    }

    /**
     * Get data collection
     *
     * @param string|null $key
     * @return DataCollection|null
     */
    public function getDataCollection($key = null)
    {
        if ($key === null) {
            return $this->collectionStorage;
        }
        return isset($this->collectionStorage[$key]) ? $this->collectionStorage[$key] : null;
    }

    /**
     * Update data collection in storage
     *
     * @param string $key
     * @param DataCollection $dataCollection
     * @return mixed
     */
    public function updateDataCollection($key, DataCollection $dataCollection)
    {
        if (isset($this->collectionStorage[$key])) {
            $this->collectionStorage[$key] = $dataCollection;
        }
    }

    /**
     * Add cloud data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function addGlobalData($key, array $data)
    {
        if (!isset($this->globalDataStorage[$key])) {
            $this->globalDataStorage[$key] = $data;
        }
    }

    /**
     * Remove cloud data in storage
     *
     * @param string $key
     * @return void
     */
    public function removeGlobalData($key)
    {
        unset($this->globalDataStorage[$key]);
    }

    /**
     * Get cloud data from storage
     *
     * @param string|null $key
     * @return array|null
     */
    public function getGlobalData($key = null)
    {
        if ($key === null) {
            return $this->globalDataStorage;
        }
        return isset($this->globalDataStorage[$key]) ? $this->globalDataStorage[$key] : null;
    }
}
