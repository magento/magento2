<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Save media gallery assets to the database
 * @api
 * @since 101.0.0
 */
interface SaveAssetsInterface
{
    /**
     * Save media asset. The saved asset can later be retrieved by path
     *
     * @param \Magento\MediaGalleryApi\Api\Data\AssetInterface[] $assets
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 101.0.0
     */
    public function execute(array $assets): void;
}
