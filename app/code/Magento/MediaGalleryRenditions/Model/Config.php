<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Model;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class responsible for providing access to Media Gallery Renditions system configuration.
 */
class Config
{
    private const XML_PATH_MEDIA_GALLERY_ENABLED = 'system/media_gallery/enabled';
    private const XML_PATH_ENABLED = 'system/media_gallery_renditions/enabled';
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH = 'system/media_gallery_renditions/width';
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH = 'system/media_gallery_renditions/height';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Initial
     */
    private $initialConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Initial $initialConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Initial $initialConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->initialConfig = $initialConfig;
    }

    /**
     * Check if the media gallery is enabled
     *
     * @return bool
     */
    public function isMediaGalleryEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_MEDIA_GALLERY_ENABLED);
    }

    /**
     * Should the renditions be inserted in the content instead of original image
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Get max width
     *
     * @return int
     */
    public function getWidth(): int
    {
        $width = $this->scopeConfig->getValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH)
            ?? $this->initialConfig->getData('default')['system']['media_gallery_renditions']['width'];
        return (int)$width;
    }

    /**
     * Get max height
     *
     * @return int
     */
    public function getHeight(): int
    {
        $height = $this->scopeConfig->getValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH)
            ?? $this->initialConfig->getData('default')['system']['media_gallery_renditions']['height'];
        return (int)$height;
    }
}
