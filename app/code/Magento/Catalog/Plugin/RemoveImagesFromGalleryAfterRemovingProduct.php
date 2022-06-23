<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;

/**
 * Responsible for deleting images from media gallery after deleting product
 */
class RemoveImagesFromGalleryAfterRemovingProduct
{
    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var ReadHandler
     */
    private $mediaGalleryReadHandler;

    /**
     * @param Gallery $galleryResource
     * @param ReadHandler $mediaGalleryReadHandler
     */
    public function __construct(Gallery $galleryResource, ReadHandler $mediaGalleryReadHandler)
    {
        $this->galleryResource = $galleryResource;
        $this->mediaGalleryReadHandler = $mediaGalleryReadHandler;
    }

    /**
     * Delete media gallery after deleting product
     *
     * @param ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param ProductInterface $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        ProductRepositoryInterface $subject,
        callable $proceed,
        ProductInterface $product
    ): bool {
        $mediaGalleryAttributeId = $this->mediaGalleryReadHandler->getAttribute()->getAttributeId();
        $mediaGallery = $this->galleryResource->loadProductGalleryByAttributeId($product, $mediaGalleryAttributeId);

        $result = $proceed($product);

        if ($mediaGallery) {
            $this->galleryResource->deleteGallery(array_column($mediaGallery, 'value_id'));
        }

        return $result;
    }
}
