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
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function get($key = null, $defaultValue = null)
    {
        $this->load();
        if ($key === null) {
            return $this->flatData;
        }

        if (array_key_exists($key, $this->flatData) && $this->flatData[$key] === null) {
            return '';
        }

        return $this->flatData[$key] ?? $defaultValue;
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
        $this->load();
        return isset($this->flatData[ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE]);
    }

    /**
     * Gets a value specified key from config data
     *
     * @param string $key
     * @return null|mixed
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getConfigData($key = null)
    {
        $this->load();

        if ($key !== null && !isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key] ?? $this->data;
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
     * Check if data from deploy files is available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     * @since 100.1.3
     */
    public function isDbAvailable()
    {
        $this->load();
        return isset($this->data['db']);
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
    private function load()
    {
        if (empty($this->data)) {
            $this->data = array_replace(
                $this->reader->load(),
                $this->overrideData ?? [],
                $this->getEnvOverride()
            );
            // flatten data for config retrieval using get()
            $this->flatData = $this->flattenParams($this->data);

            // allow reading values from env variables by convention
            // MAGENTO_DC_{path}, like db/connection/default/host =>
            // can be overwritten by MAGENTO_DC_DB__CONNECTION__DEFAULT__HOST
            foreach (getenv() as $key => $value) {
                if (false !== \strpos($key, self::MAGENTO_ENV_PREFIX)
                    && $key !== self::OVERRIDE_KEY
                ) {
                    // convert MAGENTO_DC_DB__CONNECTION__DEFAULT__HOST into db/connection/default/host
                    $flatKey = strtolower(str_replace([self::MAGENTO_ENV_PREFIX, '__'], ['', '/'], $key));
                    $this->flatData[$flatKey] = $value;
                }
            }
        }
    }

    /**
     * Array keys conversion
     *
     * Convert associative array of arbitrary depth to a flat associative array with concatenated key path as keys
     * each level of array is accessible by path key
     *
     * @param array $params
     * @param string $path
     * @param array $flattenResult
     * @return array
     * @throws RuntimeException
     */
    private function flattenParams(array $params, $path = null, array &$flattenResult = null) : array
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
}
