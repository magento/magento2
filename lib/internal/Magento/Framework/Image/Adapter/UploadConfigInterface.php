<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Image\Adapter;

/**
 * Interface UploadConfigInterface
 *
 * @deprecated moved to proper namespace and extended
 * @see \Magento\Backend\Model\Image\UploadResizeConfigInterface;
 */
interface UploadConfigInterface
{
    /**
     * Get maximum image width.
     *
     * @return int
     */
    public function getMaxWidth(): int;

    /**
     * Get maximum image height.
     *
     * @return int
     */
    public function getMaxHeight(): int;
}
