<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Application deployment configuration
 *
 * @api
 */
class DeploymentConfig
{
    /**
     * Configuration reader
     *
     * @var DeploymentConfig\Reader
     */
    private $reader;

    /**
     * Configuration data
     *
     * @var array
     */
    private $data;

    /**
     * Flattened data
     *
     * @var array
     */
    private $flatData;

    /**
     * Injected configuration data
     *
     * @var array
     */
    private $overrideData;

    /**
     * Constructor
     *
     * Data can be optionally injected in the constructor. This object's public interface is intentionally immutable
     *
     * @param DeploymentConfig\Reader $reader
     * @param array $overrideData
     */
    public function __construct(DeploymentConfig\Reader $reader, $overrideData = [])
    {
        $this->reader = $reader;
        $this->overrideData = $overrideData;
    }

    /**
     * Gets data from flattened data
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed|null
     */
    public function get($key = null, $defaultValue = null)
    {
        $this->load();
        if ($key === null) {
            return $this->flatData;
        }
        return isset($this->flatData[$key]) ? $this->flatData[$key] : $defaultValue;
    }

    /**
     * Checks if data available
     *
     * @return bool
     */
    public function isAvailable()
    {
        $this->data = null;
        $this->load();
        return isset($this->flatData[ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE]);
    }

    /**
     * Gets a value specified key from config data
     *
     * @param string $key
     * @return null|mixed
     */
    public function getConfigData($key = null)
    {
        $this->load();

        if ($key !== null && !isset($this->data[$key])) {
            return null;
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $this->data;
    }

    /**
     * Resets config data
     *
     * @return void
     */
    public function resetData()
    {
        $this->data = null;
    }

    /**
     * Check if data from deploy files is avaiable
     *
     * @return bool
     * @since 2.1.3
     */
    public function isDbAvailable()
    {
        $this->load();
        return isset($this->data['db']);
    }

    /**
     * Loads the configuration data
     *
     * @return void
     */
    private function load()
    {
        if (null === $this->data) {
            $this->data = $this->reader->load();
            if ($this->overrideData) {
                $this->data = array_replace($this->data, $this->overrideData);
            }
            // flatten data for config retrieval using get()
            $this->flatData = $this->flattenParams($this->data);
        }
    }

    /**
     * Convert associative array of arbitrary depth to a flat associative array with concatenated key path as keys
     * each level of array is accessible by path key
     *
     * @param array $params
     * @param string $path
     * @return array
     * @throws \Exception
     */
    private function flattenParams(array $params, $path = null)
    {
        $cache = [];

        foreach ($params as $key => $param) {
            if ($path) {
                $newPath = $path . '/' . $key;
            } else {
                $newPath = $key;
            }
            if (isset($cache[$newPath])) {
                throw new \Exception("Key collision {$newPath} is already defined.");
            }
            $cache[$newPath] = $param;
            if (is_array($param)) {
                $cache = array_merge($cache, $this->flattenParams($param, $newPath));
            }
        }

        return $cache;
    }
}
