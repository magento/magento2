<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;

/**
 * Save a media asset to content relation.
 * @api
 * @since 100.4.0
 */
interface SaveContentAssetLinksInterface
{
    /**
     * Save a media asset to content relation. Should be executed when media assets is added to the content
     *
     * @param ContentAssetLinkInterface[] $contentAssetLinks
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 100.4.0
     */
    public function execute(array $contentAssetLinks): void;
}
