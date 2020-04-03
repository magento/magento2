<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Get media asset ids used in the content
 * @api
 */
interface GetAssetIdsUsedInContentInterface
{
    /**
     * Get media asset ids used in the content
     *
     * @param ContentIdentityInterface $contentIdentity
     * @return int[]
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function execute(ContentIdentityInterface $contentIdentity): array;
}
