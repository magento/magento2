<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\DataProviders;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Backend\Model\Image\ImageUploadConfigInterface;

/**
 * Provides additional data for image uploader
 */
class ImageUploadConfig implements ArgumentInterface
{
    /**
     * @var ImageUploadConfigInterface
     */
    private $imageUploadConfig;

    /**
     * @param ImageUploadConfigInterface $imageUploadConfig
     */
    public function __construct(ImageUploadConfigInterface $imageUploadConfig)
    {
        $this->imageUploadConfig = $imageUploadConfig;
    }

    /**
     * Get image resize configuration
     *
     * @return int
     */
    public function getIsResizeEnabled(): int
    {
        return (int)$this->imageUploadConfig->isResizeEnabled();
    }
}
