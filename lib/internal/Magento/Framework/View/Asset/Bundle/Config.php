<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View;
use Magento\Framework\View\Asset\Bundle;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\File\FallbackContext;

class Config implements Bundle\ConfigInterface
{
    /**#@+
     * Bundle config info
     */
    const VIEW_CONFIG_MODULE = 'Js_Bundle';
    const VIEW_CONFIG_BUNDLE_SIZE_NAME = 'bundle_size';
    /**#@-*/

    /**
     * @var ListInterface
     */
    protected $themeList;

    /**
     * @var View\ConfigInterface
     */
    protected $viewConfig;

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
        return $this->viewConfig->getViewConfig([
            'area' => $assetContext->getAreaCode(),
            'themeModel' => $this->themeList->getThemeByFullPath(
                $assetContext->getAreaCode() . '/' . $assetContext->getThemePath()
            )
        ]);
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
                return (int)$size / 1024;
        }
    }
}
