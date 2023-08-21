<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Save media files data
 * @api
 */
interface ImportFilesInterface
{
    /**
     * Save media files data
     *
     * @param string[] $paths
     * @throws LocalizedException
     */
    public function execute(array $paths): void;
}
