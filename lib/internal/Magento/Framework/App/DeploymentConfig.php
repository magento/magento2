<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;

/**
 * Application deployment configuration
 *
 * @api
 * @since 100.0.2
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
    private $data = [];

    /**
     * Flattened data
     *
     * @var array
     */
    private $flatData = [];

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
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function get($key = null, $defaultValue = null)
    {
        if ($key === null) {
            if (empty($this->flatData)) {
                $this->reloadData();
            }
            return $this->flatData;
        }
        $result = $this->getByKey($key);
        if ($result === null) {
            $this->reloadData();
            $result = $this->getByKey($key);
        }
        return $result ?? $defaultValue;
    }

    /**
     * Checks if data available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function isAvailable()
    {
        return $this->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE) !== null;
    }

    /**
     * Gets a value specified key from config data
     *
     * @param string|null $key
     * @return null|mixed
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getConfigData($key = null)
    {
        if ($key === null) {
            if (empty($this->data)) {
                $this->reloadData();
            }
            return $this->data;
        }
        $result = $this->getConfigDataByKey($key);
        if ($result === null) {
            $this->reloadData();
            $result = $this->getConfigDataByKey($key);
        }
        return $result;
    }

    /**
     * Resets config data
     *
     * @return void
     */
    public function resetData()
    {
        $this->data = [];
        $this->flatData = [];
    }

    /**
     * Check if data from deploy files is available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     * @since 100.1.3
     */
    public function isDbAvailable()
    {
        return $this->getConfigData('db') !== null;
    }

    /**
     * Loads the configuration data
     *
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function reloadData(): void
    {
        $this->data = $this->reader->load();
        if ($this->overrideData) {
            $this->data = array_replace($this->data, $this->overrideData);
        }
        // flatten data for config retrieval using get()
        $this->flatData = $this->flattenParams($this->data);
    }

    /**
     * Array keys conversion
     *
     * Convert associative array of arbitrary depth to a flat associative array with concatenated key path as keys
     * each level of array is accessible by path key
     *
     * @param array $params
     * @param string|null $path
     * @param array|null $flattenResult
     * @return array
     * @throws RuntimeException
     */
    private function flattenParams(array $params, ?string $path = null, array &$flattenResult = null): array
    {
        if (null === $flattenResult) {
            $flattenResult = [];
        }

        foreach ($params as $key => $param) {
            if ($path) {
                $newPath = $path . '/' . $key;
            } else {
                $newPath = $key;
            }
            if (isset($flattenResult[$newPath])) {
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new RuntimeException(new Phrase("Key collision '%1' is already defined.", [$newPath]));
            }
            $flattenResult[$newPath] = $param;
            if (is_array($param)) {
                $this->flattenParams($param, $newPath, $flattenResult);
            }
        }

        return $flattenResult;
    }

    /**
     * @param string|null $key
     * @return mixed|null
     */
    private function getByKey(?string $key)
    {
        if (array_key_exists($key, $this->flatData) && $this->flatData[$key] === null) {
            return '';
        }

        return $this->flatData[$key] ?? null;
    }

    /**
     * @param string|null $key
     * @return mixed|null
     */
    private function getConfigDataByKey(?string $key)
    {
        return $this->data[$key] ?? null;
    }
}
