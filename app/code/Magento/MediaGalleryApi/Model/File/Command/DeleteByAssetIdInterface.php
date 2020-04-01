<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\File\Command;

/**
 * Remove the media asset file from the media storage
 * @api
 */
interface DeleteByAssetIdInterface
{
    /**
     * Remove the file of the media asset identified by the passed id from the media storage
     *
     * @param int $assetId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(int $assetId): void;
}
