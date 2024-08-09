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
    private const MAGENTO_ENV_PREFIX = 'MAGENTO_DC_';
    private const ENV_NAME_PATTERN = '~^#env\(\s*(?<name>\w+)\s*(,\s*"(?<default>[^"]+)")?\)$~';
    private const OVERRIDE_KEY = self::MAGENTO_ENV_PREFIX . '_OVERRIDE';

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
     * @var array
     */
    private $envOverrides = [];

    /**
     * @var array
     */
    private $readerLoad = [];

    /**
     * @var array
     */
    private $noConfigData = [];

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
            if (empty($this->flatData)
                || !isset($this->flatData[$key]) && !isset($this->noConfigData[$key])
                || count($this->getAllEnvOverrides())
            ) {
                $this->resetData();
                $this->reloadData();
            }

            if (!isset($this->flatData[$key])) {
                $this->noConfigData[$key] = $key;
            }
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
                $this->reloadInitialData();
            }
            return $this->data;
        }
        $result = $this->getConfigDataByKey($key);
        if ($result === null) {
            $this->reloadInitialData();
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
     * Get additional configuration from env variable MAGENTO_DC__OVERRIDE
     *
     * Data should be JSON encoded
     *
     * @return array
     */
    private function getEnvOverride() : array
    {
        $env = getenv(self::OVERRIDE_KEY);
        return !empty($env) ? (json_decode($env, true) ?? []) : [];
    }

    /**
     * Loads the configuration data
     *
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function reloadInitialData(): void
    {
        if (empty($this->readerLoad) || empty($this->data) || empty($this->flatData)) {
            $this->readerLoad = $this->reader->load();
        }
        $this->data = array_replace(
            $this->readerLoad,
            $this->overrideData ?? [],
            $this->getEnvOverride()
        );
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
        $this->reloadInitialData();
        // flatten data for config retrieval using get()
        $this->flatData = $this->flattenParams($this->data);
        $this->flatData = $this->getAllEnvOverrides() + $this->flatData;
    }

    /**
     * Load all getenv() configs once
     *
     * @return array
     */
    private function getAllEnvOverrides(): array
    {
        if (empty($this->envOverrides)) {
            // allow reading values from env variables by convention
            // MAGENTO_DC_{path}, like db/connection/default/host =>
            // can be overwritten by MAGENTO_DC_DB__CONNECTION__DEFAULT__HOST
            foreach (getenv() as $key => $value) {
                if (false !== \strpos($key, self::MAGENTO_ENV_PREFIX)
                    && $key !== self::OVERRIDE_KEY
                ) {
                    // convert MAGENTO_DC_DB__CONNECTION__DEFAULT__HOST into db/connection/default/host
                    $flatKey = strtolower(str_replace([self::MAGENTO_ENV_PREFIX, '__'], ['', '/'], $key));
                    $this->envOverrides[$flatKey] = $value;
                }
            }
        }
        return $this->envOverrides;
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

            if (is_array($param)) {
                $flattenResult[$newPath] = $param;
                $this->flattenParams($param, $newPath, $flattenResult);
            } else {
                // allow reading values from env variables
                // value need to be specified in %env(NAME, "default value")% format
                // like #env(DB_PASSWORD), #env(DB_NAME, "test")
                if ($param !== null && preg_match(self::ENV_NAME_PATTERN, $param, $matches)) {
                    $param = getenv($matches['name']) ?: ($matches['default'] ?? null);
                }

                $flattenResult[$newPath] = $param;
            }
        }

        return $flattenResult;
    }

    /**
     * Returns flat data by key
     *
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
     * Returns data by key
     *
     * @param string|null $key
     * @return mixed|null
     */
    private function getConfigDataByKey(?string $key)
    {
        return $this->data[$key] ?? null;
    }
}
