<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Api;

/**
 * Synchronize assets from the media storage to database
 * @api
 */
interface SynchronizeInterface
{
    /**
     * Synchronize assets from the media storage to database
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): void;
}
