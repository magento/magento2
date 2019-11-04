<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Asset\Command;

/**
 * Interface DeleteByPathInterface
 */
interface DeleteByPathInterface
{
    /**
     * Delete media asset by path
     *
     * @param string $mediaAssetPath
     *
     * @return void
     */
    public function execute(string $mediaAssetPath): void;
}
