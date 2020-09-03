<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditionsApi\Api;

use Magento\Framework\Exception\LocalizedException;

interface GenerateRenditionsInterface
{
    /**
     * Generate image renditions
     *
     * @param string[] $paths
     * @throws LocalizedException
     */
    public function execute(array $paths): void;
}
