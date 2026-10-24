<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\ViewModel;

use Magento\Backend\Model\Image\UploadResizeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Get configuration values for frontend image uploader.
 */
class UploadResizeConfigValue implements ArgumentInterface
{
    /**
     * @var UploadResizeConfigInterface
     */
    private UploadResizeConfigInterface $uploadResizeConfig;

    /**
     * @param UploadResizeConfigInterface $uploadResizeConfig
     */
    public function __construct(
        UploadResizeConfigInterface $uploadResizeConfig
    ) {
        $this->uploadResizeConfig = $uploadResizeConfig;
    }

    /**
     * Get maximal width value for resized image
     *
     * @return int
     */
    public function getMaxWidth(): int
    {
        return $this->uploadResizeConfig->getMaxWidth();
    }

    /**
     * Get maximal height value for resized image
     *
     * @return int
     */
    public function getMaxHeight(): int
    {
        return $this->uploadResizeConfig->getMaxHeight();
    }

    /**
     * Get config value for frontend resize
     *
     * @return bool
     */
    public function isResizeEnabled(): bool
    {
        return $this->uploadResizeConfig->isResizeEnabled();
    }
}
