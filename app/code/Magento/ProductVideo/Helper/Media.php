<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Helper;

use Magento\Framework\App\Area;

/**
 * Helper to move images from tmp to catalog directory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Media extends \Magento\Framework\App\Helper\AbstractHelper
{
    /*
     * Catalog Module
     */
    const MODULE_NAME = 'Magento_ProductVideo';

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * Theme
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $currentTheme;

    /*
     * Cached video config
     */
    protected $videoConfig;

    /**
     * @param \Magento\Framework\View\ConfigInterface $configInterface
     * @param \Magento\Framework\View\DesignInterface $designInterface
     */
    public function __construct(
        \Magento\Framework\View\ConfigInterface $configInterface,
        \Magento\Framework\View\DesignInterface $designInterface
    ) {
        $this->viewConfig = $configInterface;
        $this->currentTheme = $designInterface->getDesignTheme();
    }

    /**
     * Get video config from view.xml
     *
     * @return $this
     */
    public function getVideoConfig()
    {
        $this->videoConfig = $this->viewConfig->getViewConfig([
            'area' => Area::AREA_FRONTEND,
            'themeModel' => $this->currentTheme
        ]);

        return $this;
    }

    /**
     * Get video play params for player
     *
     * @return mixed
     */
    public function getVideoPlayAttribute()
    {
        if (!isset($this->videoConfig) || empty($this->videoConfig)) {
            $this->getVideoConfig();
        }
        return $this->videoConfig->getVarValue(self::MODULE_NAME, 'video_play');
    }

    /**
     * Get video stop params for player
     *
     * @return mixed
     */
    public function getVideoStopAttribute()
    {
        if (!isset($this->videoConfig) || empty($this->videoConfig)) {
            $this->getVideoConfig();
        }
        return $this->videoConfig->getVarValue(self::MODULE_NAME, 'video_stop');
    }

    /**
     * Get video color params for player
     *
     * @return mixed
     */
    public function getVideoColorAttribute()
    {
        if (!isset($this->videoConfig) || empty($this->videoConfig)) {
            $this->getVideoConfig();
        }
        return $this->videoConfig->getVarValue(self::MODULE_NAME, 'video_color');
    }

}
