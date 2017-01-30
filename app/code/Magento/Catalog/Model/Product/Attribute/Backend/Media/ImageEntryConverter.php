<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\Data\ImageContentInterface;

/**
 * Converter for Image media gallery type
 */
class ImageEntryConverter implements EntryConverterInterface
{
    /**
     * Media Entry type code
     */
    const MEDIA_TYPE_CODE = 'image';

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory
     */
    protected $mediaGalleryEntryFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->mediaGalleryEntryFactory = $mediaGalleryEntryFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaEntryType()
    {
        return self::MEDIA_TYPE_CODE;
    }

    /**
     * @param Product $product
     * @param array $rowData
     * @return ProductAttributeMediaGalleryEntryInterface $entry
     */
    public function convertTo(Product $product, array $rowData)
    {
        $image = $rowData;
        $productImages = $product->getMediaAttributeValues();
        if (!isset($image['types'])) {
            $image['types'] = array_keys($productImages, $image['file']);
        }
        $entry = $this->mediaGalleryEntryFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $entry,
            $image,
            '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface'
        );
        if (isset($image['value_id'])) {
            $entry->setId($image['value_id']);
        }
        return $entry;
    }

    /**
     * @param ProductAttributeMediaGalleryEntryInterface $entry
     * @return array
     */
    public function convertFrom(ProductAttributeMediaGalleryEntryInterface $entry)
    {
        $entryArray = [
            'value_id' => $entry->getId(),
            'file' => $entry->getFile(),
            'label' => $entry->getLabel(),
            'position' => $entry->getPosition(),
            'disabled' => $entry->isDisabled(),
            'types' => $entry->getTypes(),
            'media_type' => $entry->getMediaType(),
            'content' => $this->convertFromMediaGalleryEntryContentInterface($entry->getContent()),
        ];
        return $entryArray;
    }

    /**
     * @param ImageContentInterface $content
     * @return array
     */
    protected function convertFromMediaGalleryEntryContentInterface(
        ImageContentInterface $content = null
    ) {
        if ($content == null) {
            return null;
        } else {
            return [
                'data' => [
                    ImageContentInterface::BASE64_ENCODED_DATA => $content->getBase64EncodedData(),
                    ImageContentInterface::TYPE => $content->getType(),
                    ImageContentInterface::NAME => $content->getName(),
                ],
            ];
        }
    }
}
