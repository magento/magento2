<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Product\Attribute\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory;

/**
 * Converter for External Video media gallery type
 * @since 2.0.0
 */
class ExternalVideoEntryConverter extends ImageEntryConverter
{
    /**
     * Media Entry type code
     */
    const MEDIA_TYPE_CODE = 'external-video';

    /**
     * @var \Magento\Framework\Api\Data\VideoContentInterfaceFactory
     * @since 2.0.0
     */
    protected $videoEntryFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory
     * @since 2.0.0
     */
    protected $mediaGalleryEntryExtensionFactory;

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\Data\VideoContentInterfaceFactory $videoEntryFactory
     * @param ProductAttributeMediaGalleryEntryExtensionFactory $mediaGalleryEntryExtensionFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\Data\VideoContentInterfaceFactory $videoEntryFactory,
        ProductAttributeMediaGalleryEntryExtensionFactory $mediaGalleryEntryExtensionFactory
    ) {
        parent::__construct($mediaGalleryEntryFactory, $dataObjectHelper);
        $this->videoEntryFactory = $videoEntryFactory;
        $this->mediaGalleryEntryExtensionFactory = $mediaGalleryEntryExtensionFactory;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getMediaEntryType()
    {
        return self::MEDIA_TYPE_CODE;
    }

    /**
     * @param Product $product
     * @param array $rowData
     * @return ProductAttributeMediaGalleryEntryInterface
     * @since 2.0.0
     */
    public function convertTo(Product $product, array $rowData)
    {
        $entry = parent::convertTo($product, $rowData);
        $videoEntry = $this->videoEntryFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $videoEntry,
            $rowData,
            \Magento\Framework\Api\Data\VideoContentInterface::class
        );
        $entryExtension = $this->mediaGalleryEntryExtensionFactory->create();
        $entryExtension->setVideoContent($videoEntry);
        $entry->setExtensionAttributes($entryExtension);
        return $entry;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convertFrom(ProductAttributeMediaGalleryEntryInterface $entry)
    {
        $dataFromPreviewImageEntry = parent::convertFrom($entry);
        $videoContent = $entry->getExtensionAttributes()->getVideoContent();
        $entryArray = [
            'video_provider' => $videoContent->getVideoProvider(),
            'video_url' => $videoContent->getVideoUrl(),
            'video_title' => $videoContent->getVideoTitle(),
            'video_description' => $videoContent->getVideoDescription(),
            'video_metadata' => $videoContent->getVideoMetadata(),
        ];
        $entryArray = array_merge($dataFromPreviewImageEntry, $entryArray);
        return $entryArray;
    }
}
