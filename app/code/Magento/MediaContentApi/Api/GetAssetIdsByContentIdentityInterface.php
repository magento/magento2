<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Get media asset ids that are used in the piece of content identified by the specified content identity
 * @api
 * @since 100.4.0
 */
interface GetAssetIdsByContentIdentityInterface
{
    /**
     * Get media asset ids that are used in the piece of content identified by the specified content identity
     *
     * @param ContentIdentityInterface $contentIdentity
     * @return int[]
     * @throws \Magento\Framework\Exception\IntegrationException
     * @since 100.4.0
     */
    public function execute(ContentIdentityInterface $contentIdentity): array;
}
