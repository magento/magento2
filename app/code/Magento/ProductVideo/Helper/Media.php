<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Helper to get attributes for video
 */
class Media extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Catalog Module
     */
    const MODULE_NAME = 'Magento_ProductVideo';

    /**
     * Video play attribute
     */
    const NODE_CONFIG_PLAY_IF_BASE = 'play_if_base';

    /**
     * Video stop attribute
     */
    const NODE_CONFIG_SHOW_RELATED = 'show_related';

    /**
     * Video color attribute
     */
    const NODE_CONFIG_VIDEO_AUTO_RESTART = 'video_auto_restart';

    /**
     * Media config node
     */
    const MEDIA_TYPE_CONFIG_NODE = 'videos';

    /**
     * Configuration path
     */
    const XML_PATH_YOUTUBE_API_KEY = 'catalog/product_video/youtube_api_key';

    /**
     * Configuration path for video play
     */
    const XML_PATH_PLAY_IF_BASE = 'catalog/product_video/play_if_base';

    /**
     * Configuration path
     */
    const XML_PATH_SHOW_RELATED = 'catalog/product_video/show_related';
    /**
     * Configuration path
     */
    const XML_PATH_VIDEO_AUTO_RESTART = 'catalog/product_video/youtube_api_key';

    /**
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * Theme
     *
     * @var DesignInterface
     */
    protected $currentTheme;

    /**
     * Cached video config
     */
    protected $cachedVideoConfig;

    /**
     * @param ConfigInterface $configInterface
     * @param DesignInterface $designInterface
     * @param Context $context
     */
    public function __construct(
        ConfigInterface $configInterface,
        DesignInterface $designInterface,
        Context $context
    ) {
        parent::__construct($context);
        $this->viewConfig = $configInterface;
        $this->currentTheme = $designInterface->getDesignTheme();
        $this->initConfig();
    }

    /**
     * Cached video config
     *
     * @return $this
     */
    protected function initConfig()
    {
        if ($this->cachedVideoConfig === null) {
            $this->cachedVideoConfig = $this->viewConfig->getViewConfig(
                [
                    'area' => Area::AREA_FRONTEND,
                    'themeModel' => $this->currentTheme
                ]
            );
        }

        return $this;
    }

    /**
     * Get play if base video attribute
     *
     * @return mixed
     */
    public function getPlayIfBaseAttribute()
    {
        $videoAttributes = $this->cachedVideoConfig->getMediaAttributes(
            self::MODULE_NAME,
            self::MEDIA_TYPE_CONFIG_NODE,
            self::NODE_CONFIG_PLAY_IF_BASE
        );
        if (isset($videoAttributes[self::NODE_CONFIG_PLAY_IF_BASE])) {
            return $videoAttributes[self::NODE_CONFIG_PLAY_IF_BASE];
        }
    }

    /**
     * Get show related youtube attribute
     *
     * @return mixed
     */
    public function getShowRelatedAttribute()
    {
        $videoAttributes = $this->cachedVideoConfig->getMediaAttributes(
            self::MODULE_NAME,
            self::MEDIA_TYPE_CONFIG_NODE,
            self::NODE_CONFIG_SHOW_RELATED
        );
        if (isset($videoAttributes[self::NODE_CONFIG_SHOW_RELATED])) {
            return $videoAttributes[self::NODE_CONFIG_SHOW_RELATED];
        }
    }

    /**
     * Get video auto restart attribute
     *
     * @return mixed
     */
    public function getVideoAutoRestartAttribute()
    {
        $videoAttributes = $this->cachedVideoConfig->getMediaAttributes(
            self::MODULE_NAME,
            self::MEDIA_TYPE_CONFIG_NODE,
            self::NODE_CONFIG_VIDEO_AUTO_RESTART
        );
        if (isset($videoAttributes[self::NODE_CONFIG_VIDEO_AUTO_RESTART])) {
            return $videoAttributes[self::NODE_CONFIG_VIDEO_AUTO_RESTART];
        }
    }

    /**
     * Retrieve YouTube API key
     *
     * @return string
     */
    public function getYouTubeApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_YOUTUBE_API_KEY);
    }
}
