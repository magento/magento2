<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUiApi\Api;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class responsible to provide insert image details
 */
interface GetInsertImageDataInterface extends ExtensibleDataInterface
{
    /**
     * Retrieves a content (just a link or an html block) for inserting image to the content
     *
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @return null|string
     */
    public function getImageContent(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): string;

    /**
     * Retrieve size of requested file
     *
     * @param string $path
     * @return int
     */
    public function getFileSize(string $path): int;

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $path
     * @return string
     */
    public function getMimeType(string $path): string;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryUiApi\Api\GetInsertImageDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?\Magento\MediaGalleryUiApi\Api\GetInsertImageDataExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryUiApi\Api\GetInsertImageDataExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        ?\Magento\MediaGalleryUiApi\Api\GetInsertImageDataExtensionInterface $extensionAttributes
    ): void;
}
