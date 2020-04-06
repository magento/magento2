<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Remove the relation between media asset and the piece of content. I.e media asset no longer part of the content
 * @api
 */
interface UnassignAssetsInterface
{
    /**
     * Remove relation between the media asset and the content. I.e media asset no longer part of the content
     *
     * @param int[] $assetIds
     * @param ContentIdentityInterface $contentIdentity
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(ContentIdentityInterface $contentIdentity, array $assetIds): void;
}
