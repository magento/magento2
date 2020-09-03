<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditionsApi\Api;

use Magento\Framework\Exception\LocalizedException;

interface GetRenditionPathInterface
{
    /**
     * Get Renditions image path
     *
     * @param string $path
     * @return string
     * @throws LocalizedException
     */
    public function execute(string $path): string;
}
