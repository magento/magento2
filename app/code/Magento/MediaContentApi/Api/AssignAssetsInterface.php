<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Saving data represents relation between the media asset and media content
 * @api
 */
interface AssignAssetsInterface
{
    /**
     * Save relation between media asset and media content.
     *
     * @param ContentIdentityInterface $contentIdentity
     * @param int[] $assetIds
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(ContentIdentityInterface $contentIdentity, array $assetIds): void;
}
