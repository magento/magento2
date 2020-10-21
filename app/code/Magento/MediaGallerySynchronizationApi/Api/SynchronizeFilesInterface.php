<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Synchronize assets from the provided files information to database
 */
interface SynchronizeFilesInterface
{
    /**
     * Create media gallery assets based on files information and save them to database
     *
     * @param string[] $paths
     * @throws LocalizedException
     */
    public function execute(array $paths): void;
}
