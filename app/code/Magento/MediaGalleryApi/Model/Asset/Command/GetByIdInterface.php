<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Asset\Command;

/**
 * A command represents the get media gallery asset by using media gallery asset id as a filter parameter.
 * @deprecated 101.0.0 use \Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface instead
 * @see \Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface
 */
interface GetByIdInterface
{
    /**
     * Get media asset by id
     *
     * @param int $mediaAssetId
     * @return \Magento\MediaGalleryApi\Api\Data\AssetInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function execute(int $mediaAssetId): \Magento\MediaGalleryApi\Api\Data\AssetInterface;
}
