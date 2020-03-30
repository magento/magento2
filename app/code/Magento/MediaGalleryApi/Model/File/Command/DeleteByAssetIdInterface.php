<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\File\Command;

/**
 * Load Media Asset path from database by id and delete the file
 * @api
 */
interface DeleteByAssetIdInterface
{
    /**
     * Delete the file by asset ID
     *
     * @param int $assetId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(int $assetId): void;
}
