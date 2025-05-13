<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\SerializerInterface;

class ChangeDetector
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Check if the media gallery of the given product is changed
     *
     * @param Product $product
     * @return bool
     */
    public function isChanged(Product $product): bool
    {
        if ($product->isDeleted()) {
            return true;
        }

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
        foreach ($mediaGalleryImages as $imageKey => $mediaGalleryImage) {
            $origMediaGalleryImages[$imageKey] = array_intersect_key(
                $origMediaGalleryImages[$imageKey],
                $mediaGalleryImage
            );

            // client UI converts null values to empty string due to behavior of HTML encoding;
            // match this behavior before performing comparison
            foreach ($origMediaGalleryImages[$imageKey] as $key => &$value) {
                if ($value === null) {
                    $value = '';
                }

                if ($mediaGalleryImages[$imageKey][$key] === null) {
                    $mediaGalleryImages[$imageKey][$key] = '';
                }
            }
        }

        $mediaGalleryImagesSerializedString = $this->serializer->serialize($mediaGalleryImages);
        $origMediaGalleryImagesSerializedString = $this->serializer->serialize($origMediaGalleryImages);

        return $origMediaGalleryImagesSerializedString != $mediaGalleryImagesSerializedString;
    }
}
