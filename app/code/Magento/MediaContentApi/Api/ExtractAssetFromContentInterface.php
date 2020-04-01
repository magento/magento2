<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Used for extracting media asset list from a media content by the search pattern.
 * @api
 */
interface ExtractAssetFromContentInterface
{
    /**
     * Search for the media asset in content and extract it providing a list of media assets.
     *
     * @param string $content
     * @return AssetInterface[]
     */
    public function execute(string $content): array;
}
