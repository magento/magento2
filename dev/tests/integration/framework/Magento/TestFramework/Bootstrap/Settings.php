<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Bootstrap;

use Magento\Framework\Filesystem\Glob;

/**
 * Convenient access to the bootstrap settings
 */
class Settings
{
    /**
     * Base directory to be used to resolve relative paths
     *
     * @var string
     */
    private $_baseDir;

    /**
     * Key-value pairs of the settings
     *
     * @var array
     */
    private $_settings = [];

    /**
     * Constructor
     *
     * @param string $baseDir
     * @param array $settings
     * @throws \InvalidArgumentException
     */
    public function __construct($baseDir, array $settings)
    {
        if (!is_dir($baseDir)) {
            throw new \InvalidArgumentException("Base path '{$baseDir}' has to be an existing directory.");
        }
        $this->_baseDir = realpath($baseDir);
        $this->_settings = $settings;
    }

    /**
     * Retrieve a setting value as is
     *
     * @param string $settingName
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($settingName, $defaultValue = null)
    {
        return array_key_exists($settingName, $this->_settings) ? $this->_settings[$settingName] : $defaultValue;
    }

    /**
     * Interpret a setting value as a switch and return TRUE when it equals to the string "enabled" or FALSE otherwise
     *
     * @param string $settingName
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAsBoolean($settingName)
    {
        return $this->get($settingName) === 'enabled';
    }

    /**
     * Interpret a setting value as a relative file name and return absolute path to it
     *
     * @param string $settingName
     * @param string $defaultValue
     * @return string
     */
    public function getAsFile($settingName, $defaultValue = '')
    {
        $result = $this->get($settingName, $defaultValue);
        if ($result !== '') {
            $result = $this->_resolvePath($result);
        }
        return $result;
    }

    /**
     * Interpret a setting value as a file optionally falling back to the '.dist' file and return absolute path to it
     *
     * @param string $settingName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAsConfigFile($settingName)
    {
        $result = $this->getAsFile($settingName);
        if ($result !== '') {
            if (!is_file($result) && substr($result, -5) != '.dist') {
                $result .= '.dist';
            }
            if (is_file($result)) {
                return $result;
            }
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __("Setting '%1' specifies the non-existing file '%2'.", $settingName, $result)
        );
    }

    /**
     * Interpret a setting value as a semicolon-separated relative glob pattern(s) and return matched absolute paths
     *
     * @param string $settingName
     * @return array
     */
    public function getAsMatchingPaths($settingName)
    {
        $settingValue = $this->get($settingName, '');
        if ($settingValue !== '') {
            return $this->_resolvePathPattern($settingValue);
        }
        return [];
    }

    /**
     * Return an absolute path by a relative one without checking its validity
     *
     * @param string $relativePath
     * @return string
     */
    protected function _resolvePath($relativePath)
    {
        return $this->_baseDir . '/' . $relativePath;
    }

    /**
     * Resolve semicolon-separated relative glob pattern(s) to matched absolute paths
     *
     * @param string $pattern
     * @return array
     */
    protected function _resolvePathPattern($pattern)
    {
        $result = [];
        $allPatterns = preg_split('/\s*;\s*/', trim($pattern), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($allPatterns as $onePattern) {
            $onePattern = $this->_resolvePath($onePattern);
            $files = Glob::glob($onePattern, Glob::GLOB_BRACE);
            $result = array_merge($result, $files);
        }
        return $result;
    }
}
