<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Image;

/**
 * Image uploader config provider.
 */
class UploadResizeConfig implements UploadResizeConfigInterface
{
    /**
     * Config path for the maximal image width value
     */
    const XML_PATH_MAX_WIDTH_IMAGE = 'system/upload_configuration/max_width';

    /**
     * Config path for the maximal image height value
     */
    const XML_PATH_MAX_HEIGHT_IMAGE = 'system/upload_configuration/max_height';

    /**
     * Config path for the maximal image height value
     */
    const XML_PATH_ENABLE_RESIZE = 'system/upload_configuration/enable_resize';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Get maximal width value for resized image
     *
     * @return int
     */
    public function getMaxWidth(): int
    {
        return (int)$this->config->getValue(self::XML_PATH_MAX_WIDTH_IMAGE);
    }

    /**
     * Get maximal height value for resized image
     *
     * @return int
     */
    public function getMaxHeight(): int
    {
        return (int)$this->config->getValue(self::XML_PATH_MAX_HEIGHT_IMAGE);
    }

    /**
     * Get config value for frontend resize
     *
     * @return bool
     */
    public function isResizeEnabled(): bool
    {
        return (bool)$this->config->getValue(self::XML_PATH_ENABLE_RESIZE);
    }
}
