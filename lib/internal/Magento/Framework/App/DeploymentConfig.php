<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

/**
 * Application deployment configuration
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
     * Availability of deployment config file
     *
     * @var bool
     */
    private $isAvailable;

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
     * @return array|null
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
        return $this->isAvailable;
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
     * Loads the configuration data
     *
     * @return void
     */
    private function load()
    {
        if (null === $this->data) {
            $this->data = $this->reader->load();
            $this->isAvailable = !empty($this->data);
            if ($this->overrideData) {
                $this->data = array_replace($this->data, $this->overrideData);
            }
            // flatten data for config retrieval using get()
            $this->flatData = $this->reader->flattenParams($this->data);
        }
    }
}
