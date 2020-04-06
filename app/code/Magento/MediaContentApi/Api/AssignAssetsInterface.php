<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Assign a media asset to the piece of content. Should be executed when media assets is added to the content
 * @api
 */
interface AssignAssetsInterface
{
    /**
     * Assign a media asset to the piece of content. Should be executed when media assets is added to the content
     *
     * @param ContentIdentityInterface $contentIdentity
     * @param int[] $assetIds
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(ContentIdentityInterface $contentIdentity, array $assetIds): void;
}
