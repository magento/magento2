<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryRenditionsApi\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class responsible for providing access to Media Gallery Renditions system configuration.
 */
class Config
{
    /**
     * Config path for Media Gallery Renditions Width
     */
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH = 'system/media_gallery_renditions/width';

    /**
     * Config path for Media Gallery Renditions Height
     */
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH = 'system/media_gallery_renditions/height';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get max width
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH);
    }

    /**
     * Get max height
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH);
    }
}
