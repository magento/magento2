<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUiApi\Api;

use Magento\MediaGalleryUiApi\Api\Data\InsertImageDataInterface;

/**
 * Class responsible to provide insert image details
 */
interface GetInsertImageDataInterface
{
    /**
     * Retrieves a content (just a link or an html block) for inserting image to the content
     *
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @return InsertImageDataInterface
     */
    public function execute(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): InsertImageDataInterface;
}
