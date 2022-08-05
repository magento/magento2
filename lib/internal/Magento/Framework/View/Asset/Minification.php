<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;

/**
 * Helper class for static files minification related processes.
 * @api
 * @since 100.0.2
 */
class Minification
{
    /**
     * XML path for asset minification configuration
     */
    public const XML_PATH_MINIFICATION_ENABLED = 'dev/%s/minify_files';

    public const XML_PATH_MINIFICATION_EXCLUDES = 'dev/%s/minify_exclude';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var array
     */
    private $configCache = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param State $appState
     * @param string $scope
     */
    public function __construct(ScopeConfigInterface $scopeConfig, State $appState, $scope = 'store')
    {
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
        $this->scope = $scope;
    }

    /**
     * Check whether asset minification is on for specified content type
     *
     * @param string $contentType
     * @return bool
     */
    public function isEnabled($contentType)
    {
        if (!isset($this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$contentType])) {
            $this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$contentType] =
                $this->appState->getMode() != State::MODE_DEVELOPER &&
                $this->scopeConfig->isSetFlag(
                    sprintf(self::XML_PATH_MINIFICATION_ENABLED, $contentType),
                    $this->scope
                );
        }

        return $this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$contentType];
    }

    /**
     * Add 'min' suffix if minification is enabled and $filename has no one.
     *
     * @param string $filename
     * @return string
     */
    public function addMinifiedSign($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if ($this->isEnabledForArea($filename) &&
            !$this->isExcluded($filename) &&
            !$this->isMinifiedFilename($filename)
        ) {
            $filename = $filename !== null ? substr($filename, 0, -strlen($extension)) : '';
            $filename = $filename . 'min.' . $extension;
        }
        return $filename;
    }

    /**
     * Remove 'min' suffix if exists and minification is enabled
     *
     * @param string $filename
     * @return string
     */
    public function removeMinifiedSign($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if ($this->isEnabledForArea($filename) &&
            !$this->isExcluded($filename) &&
            $this->isMinifiedFilename($filename)
        ) {
            $filename = $filename !== null ? substr($filename, 0, -strlen($extension) - 4) : '';
            $filename = $filename . $extension;
        }
        return $filename;
    }

    /**
     * Is Minified Filename
     *
     * @param string $filename
     * @return bool
     */
    public function isMinifiedFilename($filename)
    {
        return $filename && substr($filename, strrpos($filename, '.') - 4, 5) == '.min.';
    }

    /**
     * Is Excluded
     *
     * @param string $filename
     * @return boolean
     */
    public function isExcluded($filename)
    {
        foreach ($this->getExcludes(pathinfo($filename, PATHINFO_EXTENSION)) as $exclude) {
            if ($filename && preg_match('/' . str_replace('/', '\/', $exclude) . '/', $filename)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get Excludes
     *
     * @param string $contentType
     * @return string[]
     */
    public function getExcludes($contentType)
    {
        if (!isset($this->configCache[self::XML_PATH_MINIFICATION_EXCLUDES][$contentType])) {
            $this->configCache[self::XML_PATH_MINIFICATION_EXCLUDES][$contentType] = [];
            $key = sprintf(self::XML_PATH_MINIFICATION_EXCLUDES, $contentType);
            $excludeValues = $this->getMinificationExcludeValues($key);
            foreach ($excludeValues as $exclude) {
                if ($exclude && trim($exclude) != '') {
                    $this->configCache[self::XML_PATH_MINIFICATION_EXCLUDES][$contentType][] = trim($exclude);
                }
            }
        }
        return $this->configCache[self::XML_PATH_MINIFICATION_EXCLUDES][$contentType];
    }

    /**
     * Get minification exclude values from configuration
     *
     * @param string $key
     * @return string[]
     */
    private function getMinificationExcludeValues($key)
    {
        $configValues = $this->scopeConfig->getValue($key, $this->scope) ?? [];
        //value used to be a string separated by 'newline' separator so we need to convert it to array
        if (!is_array($configValues)) {
            $configValuesFromString = [];
            foreach (explode("\n", $configValues) as $exclude) {
                if ($exclude && trim($exclude) != '') {
                    $configValuesFromString[] = trim($exclude);
                }
            }
            $configValues = $configValuesFromString;
        }
        return array_values($configValues);
    }

    /**
     * Check whether asset minification is on for specified content type and for area
     *
     * @param string $filename
     * @return bool
     */
    private function isEnabledForArea(string $filename): bool
    {
        $area = $this->getAreaFromPath($filename);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if ($area !== 'adminhtml') {
            $result = $this->isEnabled($extension);
        } else {
            $cacheConfigKey = $area . '_' . $extension;
            if (!isset($this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$cacheConfigKey])) {
                $this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$cacheConfigKey] =
                    $this->appState->getMode() != State::MODE_DEVELOPER &&
                    $this->scopeConfig->isSetFlag(
                        sprintf(self::XML_PATH_MINIFICATION_ENABLED, $extension),
                        'default'
                    );
            }

            $result = $this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$cacheConfigKey];
        }
        return $result;
    }

    /**
     * Get area from the path
     *
     * @param string $filename
     * @return string
     */
    private function getAreaFromPath(string $filename): string
    {
        $area = '';
        $pathParts = explode('/', $filename);
        if (isset($pathParts[0])) {
            $area = $pathParts[0];
        }
        return $area;
    }
}
