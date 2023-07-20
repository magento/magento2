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
 * It is used to add media gallery identities to product identities for invalidation purposes.
 */
class UpdateIdentities
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Flag product media gallery as changed after adding or updating image, or on product removal
     *
     * @param Product $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(Product $subject, array $result): array
    {
        if (!$subject->isObjectNew() && ($subject->isDeleted() || $this->isMediaGalleryChanged($subject))) {
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
        if (!$product->hasDataChanges()) {
            return false;
        }

        $mediaGalleryImages = $product->getMediaGallery('images') ?? [];

        $origMediaGalleryImages = $product->getOrigData('media_gallery')['images'] ?? [];

        $origMediaGalleryImageKeys = array_keys($origMediaGalleryImages);
        $mediaGalleryImageKeys = array_keys($mediaGalleryImages);

        if ($origMediaGalleryImageKeys !== $mediaGalleryImageKeys) {
            return true;
        }

        // remove keys from original array that are not in new array; some keys are omitted from the new array on save
        foreach ($mediaGalleryImages as $key => $mediaGalleryImage) {
            $origMediaGalleryImages[$key] = array_intersect_key($origMediaGalleryImages[$key], $mediaGalleryImage);
        }

        $mediaGalleryImagesSerializedString = $this->serializer->serialize($mediaGalleryImages);
        $origMediaGalleryImagesSerializedString = $this->serializer->serialize($origMediaGalleryImages);

        return $origMediaGalleryImagesSerializedString != $mediaGalleryImagesSerializedString;
    }
}
