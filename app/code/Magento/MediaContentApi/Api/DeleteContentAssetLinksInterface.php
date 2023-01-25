<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;

/**
 * Remove the relation between media asset and the piece of content. I.e media asset no longer part of the content
 * @api
 * @since 100.4.0
 */
interface DeleteContentAssetLinksInterface
{
    /**
     * Remove relation between the media asset and the content. I.e media asset no longer part of the content
     *
     * @param ContentAssetLinkInterface[] $contentAssetLinks
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @since 100.4.0
     */
    public function execute(array $contentAssetLinks): void;
}
