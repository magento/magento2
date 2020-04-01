<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

/**
 * Get media content list which is used with the specified media asset
 * @api
 */
interface GetContentWithAssetInterface
{
    /**
     * @param int $assetId
     *
     * @return array
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function execute(int $assetId): array;
}
