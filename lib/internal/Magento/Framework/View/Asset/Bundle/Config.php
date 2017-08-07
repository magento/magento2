<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View;
use Magento\Framework\View\Asset\Bundle;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

/**
 * Class Config
 * @deprecated 2.2.0 since 2.2.0
 * @see \Magento\Deploy\Config\BundleConfig
 */
class Config implements Bundle\ConfigInterface
{
    /**#@+
     * Bundle config info
     */
    const VIEW_CONFIG_MODULE = 'Js_Bundle';
    const VIEW_CONFIG_BUNDLE_SIZE_NAME = 'bundle_size';
    /**#@-*/

    /**#@-*/
    protected $themeList;

    /**
     * @var View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var ThemeProviderInterface
     * @since 2.1.1
     */
    private $themeProvider;

    /**
     * @var \Magento\Framework\Config\View[]
     * @since 2.1.1
     */
    private $config = [];

    /**
     * @param View\ConfigInterface $viewConfig
     * @param ListInterface $themeList
     */
    public function __construct(
        View\ConfigInterface $viewConfig,
        ListInterface $themeList
    ) {
        $this->viewConfig = $viewConfig;
        $this->themeList = $themeList;
    }

    /**
     * @param FallbackContext $assetContext
     * @return bool
     */
    public function isSplit(FallbackContext $assetContext)
    {
        return (bool)$this->getPartSize($assetContext);
    }

    /**
     * @param FallbackContext $assetContext
     * @return \Magento\Framework\Config\View
     */
    public function getConfig(FallbackContext $assetContext)
    {
        $themePath = $assetContext->getAreaCode() . '/' . $assetContext->getThemePath();
        if (!isset($this->config[$themePath])) {
            $this->config[$themePath] = $this->viewConfig->getViewConfig([
                'area' => $assetContext->getAreaCode(),
                'themeModel' => $this->getThemeProvider()->getThemeByFullPath(
                    $themePath
                )
            ]);
        }

        return $this->config[$themePath];
    }

    /**
     * @param FallbackContext $assetContext
     * @return int
     */
    public function getPartSize(FallbackContext $assetContext)
    {
        $size = $this->getConfig($assetContext)->getVarValue(
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
     * @return ThemeProviderInterface
     * @since 2.1.1
     */
    private function getThemeProvider()
    {
        if (null === $this->themeProvider) {
            $this->themeProvider = ObjectManager::getInstance()->get(ThemeProviderInterface::class);
        }

        return $this->themeProvider;
    }
}
