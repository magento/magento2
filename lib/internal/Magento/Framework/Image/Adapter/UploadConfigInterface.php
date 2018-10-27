<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

/**
 * Interface UploadConfigInterface
 */
interface UploadConfigInterface
{
    /**
     * @return int
     */
    public function getMaxWidth();

    /**
     * @return int
     */
    public function getMaxHeight();
}
