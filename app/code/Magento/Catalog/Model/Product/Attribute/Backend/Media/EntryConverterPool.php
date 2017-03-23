<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

/**
 * Class aggregate all Media Gallery Entry Converters
 */
class EntryConverterPool
{
    /**
     * @var EntryConverterInterface[]
     */
    private $mediaGalleryEntryConvertersCollection;

    /**
     * @param EntryConverterInterface[] $mediaGalleryEntryConvertersCollection
     */
    public function __construct(array $mediaGalleryEntryConvertersCollection)
    {
        foreach ($mediaGalleryEntryConvertersCollection as $converter) {
            if (!$converter instanceof EntryConverterInterface) {
                throw new \InvalidArgumentException(
                    __('Media Gallery converter should be an instance of EntryConverterInterface.')
                );
            }
        }
        $this->mediaGalleryEntryConvertersCollection = $mediaGalleryEntryConvertersCollection;
    }

    /**
     * Get specific converter by given media entry type
     *
     * @param string $mediaType
     * @return EntryConverterInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConverterByMediaType($mediaType)
    {
        foreach ($this->mediaGalleryEntryConvertersCollection as $converter) {
            if ($converter->getMediaEntryType() == $mediaType) {
                return $converter;
            }
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('There is no MediaGalleryEntryConverter for given type')
        );
    }
}
