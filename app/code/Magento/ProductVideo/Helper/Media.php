<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Helper to get attributes for video
 *
 * @api
 * @since 2.0.0
 */
class Media extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Catalog Module
     */
    const MODULE_NAME = 'Magento_ProductVideo';

    /**
     * Configuration path
     */
    const XML_PATH_YOUTUBE_API_KEY = 'catalog/product_video/youtube_api_key';

    /**
     * Configuration path for video play
     */
    const XML_PATH_PLAY_IF_BASE = 'catalog/product_video/play_if_base';

    /**
     * Configuration path for show related
     */
    const XML_PATH_SHOW_RELATED = 'catalog/product_video/show_related';

    /**
     * Configuration path for video auto restart
     */
    const XML_PATH_VIDEO_AUTO_RESTART = 'catalog/product_video/video_auto_restart';

    /**
     * Media config node
     */
    const MEDIA_TYPE_CONFIG_NODE = 'videos';

    /**
     * @param Context $context
     * @since 2.0.0
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get play if base video attribute
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getPlayIfBaseAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PLAY_IF_BASE);
    }

    /**
     * Get show related youtube attribute
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getShowRelatedAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SHOW_RELATED);
    }

    /**
     * Get video auto restart attribute
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getVideoAutoRestartAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_VIDEO_AUTO_RESTART);
    }

    /**
     * Retrieve YouTube API key
     *
     * @return string
     * @since 2.0.0
     */
    public function getYouTubeApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_YOUTUBE_API_KEY);
    }
}
