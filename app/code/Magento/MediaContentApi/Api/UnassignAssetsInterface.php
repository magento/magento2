<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Unassign relation between the media asset and media content where the media asset is used
 * @api
 */
interface UnassignAssetsInterface
{
    /**
     * Remove relation between the media asset and media content.
     *
     * @param int[] $assetIds
     * @param ContentIdentityInterface $contentIdentity
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(ContentIdentityInterface $contentIdentity, array $assetIds): void;
}
