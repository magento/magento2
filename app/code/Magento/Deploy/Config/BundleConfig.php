<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Config;

use Magento\Framework\View;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

/**
 * Static files bundling configuration
 *
 * Use this to get configuration settings related to JavaScript built-in bundling
 * @since 2.2.0
 */
class BundleConfig
{
    /**
     * Namespace of the bundling configuration
     */
    const VIEW_CONFIG_MODULE = 'Js_Bundle';

    /**
     * Name of the bundle file size configuration setting
     */
    const VIEW_CONFIG_BUNDLE_SIZE_NAME = 'bundle_size';

    /**
     * Interface provides theme configuration settings
     *
     * @var View\ConfigInterface
     * @since 2.2.0
     */
    private $viewConfig;

    /**
     * Theme provider interface
     *
     * Allows to retrieve theme by the them full path: "{area}/{vendor}/{theme}/{locale}"
     *
     * @var ThemeProviderInterface
     * @since 2.2.0
     */
    private $themeProvider;

    /**
     * Configuration object cache
     *
     * @var \Magento\Framework\Config\View[]
     * @since 2.2.0
     */
    private $config = [];

    /**
     * BundleConfig constructor
     *
     * @param View\ConfigInterface $viewConfig
     * @param ThemeProviderInterface $themeProvider
     * @since 2.2.0
     */
    public function __construct(
        View\ConfigInterface $viewConfig,
        ThemeProviderInterface $themeProvider
    ) {
        $this->viewConfig = $viewConfig;
        $this->themeProvider = $themeProvider;
    }

    /**
     * Max size of bundle files (in KB)
     *
     * @param string $area
     * @param string $theme
     * @return int
     * @since 2.2.0
     */
    public function getBundleFileMaxSize($area, $theme)
    {
        $size = $this->getConfig($area, $theme)->getVarValue(
            self::VIEW_CONFIG_MODULE,
            self::VIEW_CONFIG_BUNDLE_SIZE_NAME
        );
        $unit = preg_replace('/[^a-zA-Z]+/', '', $size);
        $unit = strtoupper($unit);
        switch ($unit) {
            case 'KB':
                return (int)$size;
            case 'MB':
                return (int)$size * 1024;
            default:
                return (int)($size / 1024);
        }
    }

    /**
     * Get list of directories which must be excluded
     *
     * @param string $area
     * @param string $theme
     * @return array
     * @since 2.2.0
     */
    public function getExcludedDirectories($area, $theme)
    {
        return $this->getConfig($area, $theme)->getExcludedDir();
    }

    /**
     * Get list of files which must be excluded from bundling
     *
     * @param string $area
     * @param string $theme
     * @return array
     * @since 2.2.0
     */
    public function getExcludedFiles($area, $theme)
    {
        return $this->getConfig($area, $theme)->getExcludedFiles();
    }

    /**
     * Get View Configuration object related to the given area and theme
     *
     * @param string $area
     * @param string $theme
     * @return \Magento\Framework\Config\View
     * @since 2.2.0
     */
    private function getConfig($area, $theme)
    {
        $themePath = $area . '/' . $theme;
        if (!isset($this->config[$themePath])) {
            $this->config[$themePath] = $this->viewConfig->getViewConfig([
                'area' => $area,
                'themeModel' => $this->themeProvider->getThemeByFullPath($themePath)
            ]);
        }
        return $this->config[$themePath];
    }
}
