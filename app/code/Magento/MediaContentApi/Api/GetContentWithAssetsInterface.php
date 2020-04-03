<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Get media content list which is used with the specified media asset
 * @api
 */
interface GetContentWithAssetsInterface
{
    /**
     * Get media asset to content relations by media asset id.
     *
     * @param int[] $assetIds
     * @return ContentIdentityInterface[]
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function execute(array $assetIds): array;
}
