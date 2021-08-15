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
    public function __construct(DeploymentConfig\Reader $reader, array $overrideData = [])
    {
        $this->reader = $reader;
        $this->overrideData = $overrideData;
    }

    /**
     * Gets data from flattened data
     *
     * @param string|null $key
     * @param mixed $defaultValue
     * @return mixed|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function get(string $key = null, $defaultValue = null)
    {
        $result = $this->getByKey($key);
        if ($result === null) {
            $this->reloadData();
            $result = $this->getByKey($key);
        }
        return $result ?? $defaultValue;
    }

    /**
     * Gets a value specified key from config data
     *
     * @param string|null $key
     * @return null|mixed
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getConfigData(string $key = null)
    {
        $result = $this->getConfigDataByKey($key);
        if ($result === null) {
            $this->reloadData();
            $result = $this->getConfigDataByKey($key);
        }
        return $result;
    }

    /**
     * Checks if data available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function isAvailable(): bool
    {
        return $this->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE) !== null;
    }

    /**
     * Check if data from deploy files is available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     * @since 100.1.3
     */
    public function isDbAvailable(): bool
    {
        return $this->getConfigData('db') !== null;
    }

    /**
     * Resets config data
     *
     * @return void
     */
    public function resetData(): void
    {
        $this->data = null;
        $this->flatData = null;
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
        if ($key === null) {
            return $this->flatData ?: null;
        }
        if (is_array($this->flatData) && array_key_exists($key, $this->flatData) && $this->flatData[$key] === null) {
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
        if ($key === null) {
            return $this->data;
        }
        return $this->data[$key] ?? null;
    }
}
