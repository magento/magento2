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

/**
 * Convenient access to the bootstrap settings
 */
namespace Magento\TestFramework\Bootstrap;

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
    private $_settings = array();

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
     * @throws \Magento\Framework\Exception
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
        throw new \Magento\Framework\Exception("Setting '{$settingName}' specifies the non-existing file '{$result}'.");
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
        return array();
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
        $result = array();
        $allPatterns = preg_split('/\s*;\s*/', trim($pattern), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($allPatterns as $onePattern) {
            $onePattern = $this->_resolvePath($onePattern);
            $files = glob($onePattern, GLOB_BRACE);
            $result = array_merge($result, $files);
        }
        return $result;
    }
}
