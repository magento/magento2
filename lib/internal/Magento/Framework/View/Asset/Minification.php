<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;

class Minification
{
    /**
     * XML path for asset minification configuration
     */
    const XML_PATH_MINIFICATION_ENABLED = 'dev/%s/minify_files';

    const XML_PATH_MINIFICATION_EXCLUDES = 'dev/%s/minify_exclude';

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
    private $excludes = [];

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
        return
            $this->appState->getMode() != State::MODE_DEVELOPER &&
            (bool)$this->scopeConfig->isSetFlag(
                sprintf(self::XML_PATH_MINIFICATION_ENABLED, $contentType),
                $this->scope
            );
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

        if (
            $this->isEnabled($extension) &&
            !$this->isExcluded($filename) &&
            !$this->isMinifiedFilename($filename)
        ) {
            $filename = substr($filename, 0, -strlen($extension)) . 'min.' . $extension;
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

        if (
            $this->isEnabled($extension) &&
            !$this->isExcluded($filename) &&
            $this->isMinifiedFilename($filename)
        ) {
            $filename = substr($filename, 0, -strlen($extension) - 4) . $extension;
        }
        return $filename;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function isMinifiedFilename($filename)
    {
        return substr($filename, strrpos($filename, '.') - 4, 5) == '.min.';
    }

    /**
     * @param string $filename
     * @return boolean
     */
    public function isExcluded($filename)
    {
        foreach ($this->getExcludes(pathinfo($filename, PATHINFO_EXTENSION)) as $exclude) {
            if (preg_match('/' . str_replace('/', '\/', $exclude) . '/', $filename)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $contentType
     * @return string[]
     */
    public function getExcludes($contentType)
    {
        if (!isset($this->excludes[$contentType])) {
            $this->excludes[$contentType] = [];
            $key = sprintf(self::XML_PATH_MINIFICATION_EXCLUDES, $contentType);
            foreach (explode("\n", $this->scopeConfig->getValue($key, $this->scope)) as $exclude) {
                if (trim($exclude) != '') {
                    $this->excludes[$contentType][] = trim($exclude);
                }
            };
        }
        return $this->excludes[$contentType];
    }
}
