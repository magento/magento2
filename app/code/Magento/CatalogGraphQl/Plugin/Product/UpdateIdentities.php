<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery\ResolverCacheIdentity;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * This is a plugin to \Magento\Catalog\Model\Product.
 * It is to add media gallery identities to product identities.
 */
class UpdateIdentities
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    /**
     * Set product media gallery changed after add image to the product
     *
     * @param Product $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(Product $subject, array $result): array
    {
        if ($subject->isDeleted() || $this->isMediaGalleryChanged($subject)) {
            $result[] = sprintf('%s_%s', ResolverCacheIdentity::CACHE_TAG, $subject->getId());
        }
        return $result;
    }

    /**
     * Check if the media gallery of the given product is changed
     *
     * @param Product $product
     * @return bool
     */
    private function isMediaGalleryChanged(Product $product): bool
    {
        $mediaGalleryImages = $product->getMediaGallery('images');
        $mediaGalleryImagesSerializedString = $this->serializer->serialize($mediaGalleryImages);
        $origMediaGallery = $product->getOrigData('media_gallery');
        $origMediaGalleryImages = is_array($origMediaGallery) && array_key_exists('images', $origMediaGallery)
            ? $origMediaGallery['images'] : null;
        $origMediaGalleryImagesSerializedString = $this->serializer->serialize($origMediaGalleryImages);
        return $origMediaGalleryImagesSerializedString != $mediaGalleryImagesSerializedString;
    }
}
